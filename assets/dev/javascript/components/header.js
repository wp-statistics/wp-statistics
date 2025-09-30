jQuery(document).ready(function () {
    const dropdownToggles = document.querySelectorAll('.wps-admin-header__link--has-dropdown');
    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('is-open');
                toggle.classList.toggle('is-open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        dropdownToggles.forEach(function (toggle) {
            const dropdownMenu = toggle.nextElementSibling;
            if (dropdownMenu && !toggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('is-open');
            }
        });
    });

    const targetElement = document.querySelector('.wp-header-end');
    const noticeElement = document.querySelector('.notice.notice-warning.update-nag');
     if (targetElement && noticeElement) {
         targetElement.parentNode.insertBefore(noticeElement, targetElement.nextSibling);
    }

    const exportBtn = document.querySelector(".wps-premium-btn__export");
    const exportDropdown = document.querySelector(".wps-export-dropdown");
    const overlay = document.createElement("div");

    if(exportDropdown){
        overlay.className = "export-overlay";
        document.body.appendChild(overlay);

        exportBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            exportDropdown.classList.toggle("active");
            overlay.classList.toggle("active");
            exportBtn.parentElement.classList.toggle("drop-down-open");
        });

        overlay.addEventListener("click", () => {
            exportDropdown.classList.remove("active");
            overlay.classList.remove("active");
            exportBtn.parentElement.classList.remove("drop-down-open");
        });

        exportDropdown.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", async (e) => {
                e.preventDefault();

                const url = link.dataset.url;
                const originalHTML = link.innerHTML;
                link.classList.add("loading");
                link.innerHTML = `<span class="spinner"></span> Preparing your file…`;

                try {
                    const res = await fetch(link.dataset.url);
                    const {download_url} = await res.json();
                    const a = document.createElement("a");
                    a.href = download_url;
                    a.download = "";
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                } catch (err) {
                    console.error(err)
                } finally {
                    link.innerHTML = originalHTML;
                    exportDropdown.classList.remove("active");
                    overlay.classList.remove("active");
                    link.classList.remove("loading");
                    exportBtn.parentElement.classList.remove("drop-down-open");
                }
            });
        });
    }
});


