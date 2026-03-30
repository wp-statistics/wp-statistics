<?php

namespace WP_Statistics\Service\Messaging\Provider;

use Exception;
use WP_Statistics\Components\View;

/**
 * Handles construction and sending of e‑mail messages through `wp_mail()`.
 *
 * The class exposes fluent setters for recipients, headers, subject, body,
 * templates and attachments, then dispatches the message via {@see wp_mail()}.
 *
 * @package WP_Statistics\Service\Messaging\Provider
 * @since 15.0.0
 */
class MailProvider
{
    /**
     * List of main recipients.
     *
     * @var string[]
     */
    private $to = [];

    /**
     * Carbon‑copy recipients that will be visible to everyone.
     *
     * @var string[]
     */
    private $cc = [];

    /**
     * Blind‑carbon‑copy recipients that stay hidden from other recipients.
     *
     * @var string[]
     */
    private $bcc = [];

    /**
     * Extra mail headers (e.g. `Reply‑To`, custom IDs).
     *
     * @var string[]
     */
    private $headers = [];

    /**
     * Controls whether the message is sent as HTML (`true`) or plain text (`false`).
     *
     * @var bool
     */
    private $sendAsHtml = true;

    /**
     * “From” field value, accepts either `you@example.com` or
     * the full `Name <address>` format.
     *
     * @var string
     */
    private $from = '';

    /**
     * Optional template file rendered above the main body.
     *
     * @var string|false
     */
    private $headerTemplate = false;

    /**
     * Variables injected into the header template.
     *
     * @var array<string,mixed>
     */
    private $headerVars = [];

    /**
     * Main template file for the message body.
     *
     * @var string|false
     */
    private $template = false;

    /**
     * Variables injected into the main template.
     *
     * @var array<string,mixed>
     */
    private $templateVars = [];

    /**
     * Optional template file rendered after the main body.
     *
     * @var string|false
     */
    private $footerTemplate = false;

    /**
     * Variables injected into the footer template.
     *
     * @var array<string,mixed>
     */
    private $footerVars = [];

    /**
     * Final body contents sent to `wp_mail()`.
     *
     * @var string
     */
    public $body = '';

    /**
     * Full paths of files attached to the message.
     *
     * @var string[]
     */
    public $attachments = [];

    /**
     * Subject line of the e‑mail.
     *
     * @var string
     */
    public $subject = '';

    /**
     * Returns the current instance to allow fluent chaining.
     *
     * @return self
     */
    public function init()
    {
        return $this;
    }

    /**
     * Define primary recipients.
     *
     * @param string|array $to Comma‑separated list or array of e‑mail addresses.
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = is_array($to) ? $to : explode(',', $to);
        return $this;
    }

    /**
     * Get the list of primary recipients.
     *
     * @return array<string>
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set carbon‑copy recipients.
     *
     * @param string|array $cc Single address or list.
     * @return $this
     */
    public function setCc($cc)
    {
        $this->cc = is_array($cc) ? $cc : [$cc];
        return $this;
    }

    /**
     * Retrieve carbon‑copy recipients.
     *
     * @return array<string>
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set blind‑carbon‑copy recipients.
     *
     * @param string|array $bcc Single address or list.
     * @return $this
     */
    public function setBcc($bcc)
    {
        $this->bcc = is_array($bcc) ? $bcc : [$bcc];
        return $this;
    }

    /**
     * Retrieve blind‑carbon‑copy recipients.
     *
     * @return array<string>
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Define the e‑mail subject.
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get the current subject line.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the "From" header.
     *
     * @param string $from Address formatted as `Name <user@example.com>` or just the e‑mail.
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Define custom headers.
     *
     * @param string|array $headers Header string or array of header strings.
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = is_array($headers) ? $headers : [$headers];
        return $this;
    }

    /**
     * Retrieve custom headers.
     *
     * @return array<string>
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Callback for `wp_mail_content_type` filter.
     *
     * @return string MIME content‑type.
     */
    public function htmlFilter()
    {
        return 'text/html';
    }

    /**
     * Toggle HTML content‑type.
     *
     * @param bool $html True for HTML, false for plain text.
     * @return $this
     */
    public function sendAsHtml($html)
    {
        $this->sendAsHtml = $html;
        return $this;
    }

    /**
     * Attach one or multiple files.
     *
     * @param string|array $path Absolute path or array of paths.
     * @return $this
     * @throws Exception If a path does not exist.
     */
    public function setAttach($path)
    {
        $paths = is_array($path) ? $path : [$path];

        foreach ($paths as $p) {
            if (!file_exists($p)) {
                throw new Exception('Attachment not found at ' . esc_html($p));
            }
            $this->attachments[] = $p;
        }

        return $this;
    }

    /**
     * Specify an optional header template.
     *
     * @param string $template Absolute path to a PHP/HTML template.
     * @param array<string,mixed> $vars Variables passed to the template.
     * @return $this
     * @throws Exception If template is not found.
     */
    public function templateHeader($template, $vars = [])
    {
        if (!file_exists($template)) {
            throw new Exception('Header template not found');
        }
        $this->headerTemplate = $template;
        $this->headerVars     = $vars;
        return $this;
    }

    /**
     * Define the plain body text or HTML fragment.
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Set the main message template and variables.
     *
     * @param string|bool $template Absolute path or `true` to use default.
     * @param array<string,mixed> $vars Template variables.
     * @return $this
     * @throws Exception If the template path is invalid.
     */
    public function setTemplate($template, $vars = [])
    {
        if ($template === true) {
            /**
             * Filter the default email layout view name or absolute path.
             *
             * Return a view name (e.g. 'emails/layout') resolved relative to
             * WP_STATISTICS_DIR/views/, or an absolute path ending in .php/.html.
             *
             * @since 15.0.0
             * @param string $template View name or absolute path.
             */
            $template = apply_filters('wp_statistics_email_template_layout', 'emails/layout');
        }

        $this->template = $template ?: false;

        $isRtl = is_rtl();

        $defaultVars = [
            'logo'        => '',
            'site_url'    => home_url(),
            'site_title'  => get_bloginfo('name'),
            'footer_text' => '',
            'logo_image'  => apply_filters('wp_statistics_email_logo', defined('WP_STATISTICS_URL') ? WP_STATISTICS_URL . 'public/images/logo-statistics-header-blue.png' : ''),
            'logo_url'    => apply_filters('wp_statistics_email_logo_url', get_bloginfo('url')),
            'copyright'   => apply_filters(
                'wp_statistics_email_footer_copyright',
                View::load('emails/copyright', [], true)
            ),
            'is_rtl'      => $isRtl,
        ];

        $mergedArgs = wp_parse_args($vars, $defaultVars);

        $this->templateVars = $mergedArgs;
        return $this;
    }

    /**
     * Specify an optional footer template.
     *
     * @param string $template Absolute path.
     * @param array<string,mixed> $vars Variables passed to the footer.
     * @return $this
     * @throws Exception If template is not found.
     */
    public function setTemplateFooter($template, $vars = [])
    {
        if (!file_exists($template)) {
            throw new Exception('Footer template not found');
        }
        $this->footerTemplate = $template;
        $this->footerVars     = $vars;
        return $this;
    }

    /**
     * Render header, main, and footer templates into a single HTML string.
     *
     * @return string
     */
    public function render()
    {
        return $this->renderPart('header') .
            $this->renderPart('main') .
            $this->renderPart('footer');
    }

    /**
     * Render a specific template section.
     *
     * @param string $part Accepts 'header', 'main', or 'footer'.
     * @return string
     * @throws Exception On unknown extension.
     */
    private function renderPart($part)
    {
        switch ($part) {
            case 'header':
                $file = $this->headerTemplate;
                $vars = $this->headerVars;
                break;
            case 'footer':
                $file = $this->footerTemplate;
                $vars = $this->footerVars;
                break;
            default:
                $file = $this->template;
                $vars = $this->templateVars;
        }

        if (!$file) {
            return '';
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // View name (no extension) — resolve via View::load()
        if (empty($ext)) {
            return View::load($file, $vars, true) ?: '';
        }

        // Absolute PHP template path
        if ($ext === 'php') {
            return View::renderFile($file, $vars);
        }

        // Remote HTML file
        if ($ext === 'html') {
            $string = wp_remote_retrieve_body(wp_remote_get($file));
            return $this->parseMustache($string, $vars);
        }

        throw new Exception('Unknown template extension: ' . esc_html($ext));
    }

    /**
     * Lightweight replacement for Mustache‑style {{token}} parsing.
     *
     * @param string $string Template content.
     * @param array<string,mixed> $vars Replacement map.
     * @return string
     */
    private function parseMustache($string, $vars)
    {
        preg_match_all('/\{\{\s*.+?\s*\}\}/', $string, $matches);

        foreach ($matches[0] as $token) {
            $key = trim(str_replace(['{', '}'], '', $token));
            if (isset($vars[$key]) && !is_array($vars[$key])) {
                $string = str_replace($token, $vars[$key], $string);
            }
        }

        return $string;
    }

    /**
     * Interpolate variables inside the subject line.
     *
     * @return string
     */
    public function buildSubject()
    {
        return $this->parseMustache(
            $this->subject,
            array_merge($this->headerVars, $this->templateVars, $this->footerVars)
        );
    }

    /**
     * Assemble headers into a CRLF‑separated string for `wp_mail()`.
     *
     * @return string
     */
    private function buildHeaders()
    {
        $headers = implode("\r\n", $this->headers) . "\r\n";

        foreach ($this->bcc as $bcc) {
            $headers .= "Bcc: {$bcc}\r\n";
        }
        foreach ($this->cc as $cc) {
            $headers .= "Cc: {$cc}\r\n";
        }
        if ($this->from !== '') {
            $headers .= "From: {$this->from}\r\n";
        }

        return $headers;
    }

    /**
     * Finalise message parts and dispatch via `wp_mail()`.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If mandatory fields are missing.
     */
    public function send()
    {
        if (empty($this->to)) {
            throw new Exception('At least one recipient must be set');
        }

        // Render template if provided.
        if ($this->template) {
            $this->body = $this->render();
        }

        if ($this->sendAsHtml) {
            add_filter('wp_mail_content_type', [$this, 'htmlFilter']);
        }

        /**
         * Action hook fired before sending an email.
         *
         * @since 15.0.0
         * @param MailProvider $mailProvider The mail provider instance.
         */
        do_action('wp_statistics_mail_before_send', $this);

        /**
         * Filter the email recipients.
         *
         * @since 15.0.0
         * @param array $recipients List of email addresses.
         * @param MailProvider $mailProvider The mail provider instance.
         */
        $recipients = apply_filters('wp_statistics_mail_recipients', $this->to, $this);

        /**
         * Filter the email subject.
         *
         * @since 15.0.0
         * @param string $subject The email subject.
         * @param MailProvider $mailProvider The mail provider instance.
         */
        $subject = apply_filters('wp_statistics_mail_subject', $this->buildSubject(), $this);

        /**
         * Filter the email body content.
         *
         * @since 15.0.0
         * @param string $body The email body HTML or text.
         * @param MailProvider $mailProvider The mail provider instance.
         */
        $body = apply_filters('wp_statistics_mail_body', $this->body, $this);

        /**
         * Filter the email headers.
         *
         * @since 15.0.0
         * @param string $headers The email headers string.
         * @param MailProvider $mailProvider The mail provider instance.
         */
        $headers = apply_filters('wp_statistics_mail_headers', $this->buildHeaders(), $this);

        /**
         * Filter the email attachments.
         *
         * @since 15.0.0
         * @param array $attachments List of file paths to attach.
         * @param MailProvider $mailProvider The mail provider instance.
         */
        $attachments = apply_filters('wp_statistics_mail_attachments', $this->attachments, $this);

        $result = wp_mail($recipients, $subject, $body, $headers, $attachments);

        /**
         * Action hook fired after sending an email.
         *
         * @since 15.0.0
         * @param bool $result Whether the email was sent successfully.
         * @param MailProvider $mailProvider The mail provider instance.
         */
        do_action('wp_statistics_mail_after_send', $result, $this);

        return $result;
    }
}