<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .custom-swal-container {
            border-radius: 15px !important;
        }
        .custom-swal-title {
            background-color: #e9eff7 !important;
            color: #334155 !important;
            padding: 20px 20px !important;
            margin: 15px !important;
            border-radius: 10px !important;
            font-size: 1.5rem !important;
            width: calc(100% - 30px) !important;
            display: block !important;
            text-align: left !important;
            font-weight: 600;
        }
        .custom-swal-html {
            color: #64748b !important;
            font-size: 1.1rem !important;
            margin-top: 25px !important;
            margin-bottom: 25px !important;
        }

        .swal2-timer-progress-bar {
            background: #f59e0b !important;
            padding: 4px 4px !important;
        }
    </style>

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia

¿<script>
    (function() {
    const MINUTES_TOTAL = 120;
    const WARNING_SECONDS = 30;
    const INACTIVITY_TIME = MINUTES_TOTAL * 60 * 1000;
    const WARNING_THRESHOLD = INACTIVITY_TIME - (WARNING_SECONDS * 1000);

    let interval = null;
    let alertActive = false;
    let isMonitored = false;

    function isAuthPage() {
        return window.location.pathname === '/login' || window.location.pathname === '/register';
    }

    function stopMonitoring() {
        isMonitored = false;
        if (interval) clearInterval(interval);
        localStorage.removeItem('last_activity');
        localStorage.removeItem('is_logging_out');
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        events.forEach(e => window.removeEventListener(e, updateLastActivity, true));
    }

    function logout() {
        if (alertActive) Swal.close();

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        console.log('Ejecutando salida segura...');

        fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.status === 419) {
                localStorage.setItem('is_logging_out', 'true');
                window.location.reload();
            } else {
                stopMonitoring();
                localStorage.clear();
                window.location.replace('/login');
            }
        }).catch(() => {
            stopMonitoring();
            localStorage.clear();
            window.location.replace('/login');
        });
    }

    function updateLastActivity() {
        if (!alertActive && !isAuthPage() && localStorage.getItem('is_logging_out') !== 'true') {
            localStorage.setItem('last_activity', Date.now().toString());
        }
    }

    function checkInactivity() {
        if (isAuthPage()) {
            stopMonitoring();
            return;
        }

        if (localStorage.getItem('is_logging_out') === 'true') {
            logout();
            return;
        }

        const lastActivityStr = localStorage.getItem('last_activity');
        if (!lastActivityStr) {
            updateLastActivity();
            return;
        }

        const lastActivity = parseInt(lastActivityStr);
        const diff = Date.now() - lastActivity;

        if (diff >= INACTIVITY_TIME) {
            logout();
            return;
        }

        if (diff >= WARNING_THRESHOLD && !alertActive) {
            alertActive = true;

            let timerInterval;
            Swal.fire({
                title: '¿Sigues ahí?',
                html: 'Tu sesión se cerrará por inactividad en <br><b></b> segundos.',
                timer: INACTIVITY_TIME - diff,
                timerProgressBar: true,
                confirmButtonText: 'Seguir conectado',
                allowOutsideClick: false,
                didOpen: () => {
                    const b = Swal.getHtmlContainer().querySelector('b');
                    timerInterval = setInterval(() => {
                        const timeLeft = Swal.getTimerLeft();
                        const remaining = Math.ceil((timeLeft || 0) / 1000);
                        b.textContent = remaining > 0 ? remaining : 0;

                        const currentLast = parseInt(localStorage.getItem('last_activity') || "0");
                        if (Date.now() - currentLast < WARNING_THRESHOLD) {
                            Swal.close();
                        }
                    }, 500);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    alertActive = false;
                    updateLastActivity();
                    if (window.Inertia) {
                        window.Inertia.reload({ preserveScroll: true });
                    } else {
                        fetch('/api/user').catch(() => {});
                    }
                } else {
                    logout();
                }
            });
        }
    }

    function startMonitoring(user) {
        if (user && !isAuthPage()) {
            if (isMonitored) return;
            isMonitored = true;
            if (localStorage.getItem('is_logging_out') !== 'true') {
                updateLastActivity();
            }
            if (interval) clearInterval(interval);
            interval = setInterval(checkInactivity, 1000);

            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
            events.forEach(e => {
                window.removeEventListener(e, updateLastActivity, true);
                window.addEventListener(e, updateLastActivity, true);
            });
        } else {
            stopMonitoring();
        }
    }

    document.addEventListener('inertia:success', (event) => {
        const user = event.detail.page.props.auth?.user;
        startMonitoring(user);
    });

    window.addEventListener('load', () => {
        try {
            const el = document.getElementById('app');
            if (el && el.dataset.page) {
                const pageData = JSON.parse(el.dataset.page);
                startMonitoring(pageData.props.auth?.user);
            }
        } catch (e) { console.error(e); }
    });
})();
</script>

</body>

</html>
