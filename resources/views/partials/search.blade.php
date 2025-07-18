<!-- Topbar Search -->
<style>
    .search-list {
        position: absolute;
        width: 100%;
        background: white;
        margin-top: 10%;
        border-radius: 10px;
        max-height: 200px;
        overflow-y: scroll;
        scrollbar-width: none;
        display: none;
        z-index: 99;
    }

    .search-list ul {
        padding: 0px;
    }

    .search-list ul li a {
        font-size: 14px;
        font-weight: 500;
        color: black;
        padding: 2px;
        text-decoration: none;
    }

    .search-list ul li {
        cursor: pointer;
        border-bottom: 1px solid #e9e9e9;
        width: auto;
        list-style: none;
        padding: 8px;
    }

    div.search-list.search-val {
        display: block !important;
    }

    .search-list ul li:hover {
        background-color: #4d72de !important;
    }

    .search-list ul li:hover a {
        color: #ffffff !important;
    }
</style>
<form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" onsubmit="return false">
    <div class="input-group">
        <input type="text" name="query" class="form-control bg-light border-0 small" placeholder="Search for..."
            aria-label="Search" aria-describedby="basic-addon2" autocomplete="off">
        <div class="input-group-append">
            <button class="btn btn-primary" type="button">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
        <div class="shadow-lg search-list">
            <ul></ul>
        </div>
    </div>
</form>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const searchEle = document.querySelector('[name=query]');
        const dataDiv = document.querySelector('.search-list');
        const dataList = document.querySelector('.search-list ul');
        let allLinks = [];

        // Debounce function to limit the rate at which a function can fire.
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Cache all links on page load to avoid repeatedly querying the DOM.
        function cacheLinks() {
            allLinks = [...document.querySelectorAll('a:not(.search-list ul li a)')];
        }

        // Function to handle the search input
        function handleSearchInput(e) {
            e.preventDefault();
            dataList.innerHTML = '';
            const query = this.value.toLowerCase().trim();

            if (!query) {
                dataDiv.classList.remove('search-val');
                return;
            }

            dataDiv.classList.add('search-val');
            const result = allLinks.filter(a => a.textContent.toLowerCase().trim().includes(query));

            if (result.length) {
                result.forEach(a => {
                    if (a.href.trim() !== 'javascript:void(0)' &&
                        a.href.trim() !== 'javascript:;' &&
                        a.textContent.trim() !== '...' &&
                        a.textContent.trim() !== '' &&
                        !a.href.includes(window.location.href)) {

                        const url = new URL(a.href);
                        const link = document.createElement('a');
                        link.href = a.href;
                        link.classList.add('w-100');
                        link.classList.add('h-100');
                        link.textContent = a.textContent.trim().length >= 30 ? (a.textContent.trim()
                            .substring(0, 20) + '...') : a.textContent;

                        if (url.host !== window.location.host) {
                            link.target = "_blank";
                            link.onclick = function() {
                                return confirm(
                                    'This is an external link, Are you sure you want to continue?'
                                );
                            };
                        }

                        const li = document.createElement('li');
                        li.onclick = function() {
                            this.querySelector('a').click();
                        };
                        li.appendChild(link);
                        dataList.appendChild(li);
                    }
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'No matching results found.';
                li.classList.add('text-danger', 'fs-6');
                dataList.appendChild(li);
            }
        }

        // Initialize the script
        cacheLinks();
        if (searchEle) {
            searchEle.addEventListener('input', debounce(handleSearchInput, 300));
        }

        document.addEventListener('click', function(event) {
            const targetElement = document.querySelector('.shadow-lg.search-list.search-val');

            if (targetElement && !targetElement.contains(event.target)) {
                // Clicked outside the element
                dataList.innerHTML = '';
                dataDiv.classList.remove('search-val');
            }
        });
    });
</script>
