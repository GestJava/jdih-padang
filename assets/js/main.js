/* JDIH Main JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    /**
     * Back to top button functionality
     * --------------------------------
     * Shows/hides the back to top button based on scroll position
     * and handles smooth scrolling to top when clicked
     */
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (backToTopButton) {
        // Add scroll event listener
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        // Add click event listener
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    /**
     * Advanced Search Panel Functionality
     * ----------------------------------
     * Toggles the advanced search panel visibility
     * Handles popular search tag clicks
     * Manages search form submission behavior
     */
    const advancedSearchToggle = document.getElementById('advancedSearchToggle');
    const advancedSearchPanel = document.getElementById('advancedSearchPanel');
    const searchForm = document.getElementById('searchForm');
    
    if (advancedSearchToggle && advancedSearchPanel && searchForm) {
        // Toggle panel visibility with animation
        advancedSearchToggle.addEventListener('click', function() {
            if (advancedSearchPanel.classList.contains('active')) {
                advancedSearchPanel.classList.remove('active');
                advancedSearchToggle.innerHTML = '<i class="fas fa-sliders-h me-1"></i> Advanced Search';
            } else {
                advancedSearchPanel.classList.add('active');
                advancedSearchToggle.innerHTML = '<i class="fas fa-times me-1"></i> Simple Search';
            }
        });
        
        // Handle popular search tags click
        document.querySelectorAll('.search-tag').forEach(tag => {
            tag.addEventListener('click', function(e) {
                e.preventDefault();
                const keyword = this.textContent.trim();
                const searchInput = searchForm.querySelector('input[name="keyword"]');
                searchInput.value = keyword;
                searchForm.submit();
            });
        });
        
        // Prevent form submission on Enter key in the search field when advanced panel is open
        const searchInput = searchForm.querySelector('input[name="keyword"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && advancedSearchPanel.classList.contains('active')) {
                    e.preventDefault();
                    console.log("Form submitted");
                }
            });
        }
    }

    // Multi-level dropdown functionality for mobile
    document.querySelectorAll('.dropdown-submenu .dropdown-toggle').forEach(function(element) {
        element.addEventListener('click', function (e) {
            let submenu = this.nextElementSibling;
            if (submenu && submenu.classList.contains('dropdown-menu')) {
                e.stopPropagation();
                e.preventDefault();
                submenu.classList.toggle('show');

                // Close other open submenus
                let parent = this.closest('.dropdown-menu');
                if (parent) {
                    parent.querySelectorAll('.dropdown-menu.show').forEach(function(otherSubmenu) {
                        if (otherSubmenu !== submenu) {
                            otherSubmenu.classList.remove('show');
                        }
                    });
                }
            }
        });
    });

    // Close submenus when clicking outside
    window.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-submenu .dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu .dropdown-menu.show').forEach(function(submenu) {
                submenu.classList.remove('show');
            });
        }
    });

    // Statistik Interaktif (Chart.js)
    if (typeof Chart !== 'undefined') {
        // Statistik Dokumen per Tahun
        const ctxTahunan = document.getElementById('statistikTahunanChart');
        if (ctxTahunan) {
            new Chart(ctxTahunan, {
                type: 'bar',
                data: {
                    labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
                    datasets: [{
                        label: 'Jumlah Dokumen',
                        data: [120, 150, 180, 210, 250, 300],
                        backgroundColor: 'rgba(13,110,253,0.7)',
                        borderColor: 'rgba(13,110,253,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
        // Statistik Dokumen per Jenis
        const ctxJenis = document.getElementById('statistikJenisChart');
        if (ctxJenis) {
            new Chart(ctxJenis, {
                type: 'pie',
                data: {
                    labels: ['Peraturan Daerah', 'Peraturan Bupati', 'Keputusan Bupati', 'Surat Edaran'],
                    datasets: [{
                        data: [120, 80, 60, 40],
                        backgroundColor: [
                            'rgba(13,110,253,0.7)',
                            'rgba(25,135,84,0.7)',
                            'rgba(255,193,7,0.7)',
                            'rgba(220,53,69,0.7)'
                        ],
                        borderColor: [
                            'rgba(13,110,253,1)',
                            'rgba(25,135,84,1)',
                            'rgba(255,193,7,1)',
                            'rgba(220,53,69,1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: false }
                    }
                }
            });
        }
    }
});
