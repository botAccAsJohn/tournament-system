<nav class="navbar">
    <ul class="nav-list">
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <!-- Admin navigation -->
            <li><a href="./welcome.php">Home</a></li>
            <li><a href="./tournament.php">Tournament</a></li>
            <li><a href="./team.php">Team</a></li>
            <li><a href="./player.php">Players</a></li>
            <li><a href="./match.php">Match</a></li>
            <li><a href="./matchScore.php">Match Score</a></li>
            <li><a href="./leaderboard.php">Leaderboard</a></li>
        <?php else: ?>
            <!-- Player navigation -->
            <li><a href="./playerDashboard.php">Home</a></li>
            <li><a href="./playerView/tournaments.php">Tournaments</a></li>
            <li><a href="./playerView/matches.php">Matches</a></li>
            <li><a href="./leaderboard.php">Leaderboard</a></li>
        <?php endif; ?>
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
        opacity: 1;
        font-family: Arial, sans-serif;
        font-size: 15px;
        transition: 0.2s;
    }

    .nav-list a:hover {
        color: #00bfff;
    }

    .nav-list a.active {
        color: #7dd3fc;
    }
</style>

<script>
    const links = document.querySelectorAll(".nav-list a");
    const currentPath = window.location.pathname;
    links.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        if (linkPath === currentPath) {
            link.classList.add("active");
        }
    });
</script>