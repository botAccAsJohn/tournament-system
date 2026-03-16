<nav class="navbar">
    <ul class="nav-list">
        <li><a href="../playerDashboard.php">Home</a></li>
        <li><a href="./tournaments.php">Tournaments</a></li>
        <li><a href="./matches.php">Matches</a></li>
        <li><a href="../leaderboard.php">Leaderboard</a></li>
    </ul>
</nav>
<style>
    .navbar {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 18px 25px;
        border-radius: 40px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px) saturate(120%);
        -webkit-backdrop-filter: blur(18px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow:
            0 10px 30px rgba(0, 0, 0, 0.35),
            inset 0 0 12px rgba(255, 255, 255, 0.08);
        z-index: 1000;
    }
    .nav-list {
        display: flex;
        gap: 25px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .nav-list a {
        text-decoration: none;
        color: #000;
        font-family: Arial, sans-serif;
        font-size: 15px;
        transition: 0.2s;
    }
    .nav-list a:hover  { color: #00bfff; }
    .nav-list a.active { color: #7dd3fc; }
</style>
<script>
    document.querySelectorAll('.nav-list a').forEach(link => {
        if (new URL(link.href).pathname === window.location.pathname) {
            link.classList.add('active');
        }
    });
</script>
