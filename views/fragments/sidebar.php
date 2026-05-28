<nav id="sidebar">
    <div class="position-sticky text-center pt-3">
        <div class="profile-avatar-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center bg-secondary rounded-circle text-white">
            👤
        </div>
        <h5 class="fw-bold text-white mb-4"><?= htmlspecialchars($username ?? 'User'); ?></h5>
        <hr class="text-white-50 mb-4">

        <div class="nav flex-column nav-pills text-start">
            <a class="nav-link btn w-100 text-start py-3 mb-2 text-white <?= ($activeTab === 'dashboard') ? 'btn-coral active' : 'bg-transparent' ?>" href="/myspace">
                <span class="me-2">🎯</span> Recommendations
            </a>

            <a class="nav-link btn w-100 text-start py-3 mb-2 text-white <?= ($activeTab === 'bookmarks') ? 'btn-coral active' : 'bg-transparent' ?>" href="/myspace?tab=bookmarks">
                <span class="me-2">❤️</span> Bookmarks
            </a>

            <a class="nav-link btn w-100 text-start py-3 mb-2 text-white <?= ($activeTab === 'settings') ? 'btn-coral active' : 'bg-transparent' ?>" href="/myspace?tab=settings">
                <span class="me-2">⚙️</span> Settings
            </a>

            <a class="nav-link btn w-100 text-start py-3 mt-4 text-danger bg-transparent" href="/logout">
                <span class="me-2">↪️</span> Logout
            </a>
        </div>
    </div>
</nav>
