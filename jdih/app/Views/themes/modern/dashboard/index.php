<!-- Simple Dashboard CSS Fix -->
<style>
    .dashboard .card-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    .dashboard .card-header .dropdown {
        margin-left: auto !important;
    }

    .dashboard .dropdown-menu {
        right: 0 !important;
        left: auto !important;
    }
</style>

<!-- Dashboard Content -->
<div class="dashboard"><?= $this->renderSection('content') ?></div>