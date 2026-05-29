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

<script>
    (function() {
        const MINUTES_TOTAL = 120;
        const WARNING_SECONDS = 30;
        const INACTIVITY_TIME = (MINUTES_TOTAL * 60 * 1000) - 10000;
        const WARNING_THRESHOLD = INACTIVITY_TIME - (WARNING_SECONDS * 1000);
        let interval = null;
        let alertActive = false;
        let isMonitored = false;

        function logout() {
            if (alertActive) {
                Swal.close();
                alertActive = false;
            }
            console.log("Sesión expirada. Cerrando...");
            localStorage.removeItem('last_activity');
            if (interval) clearInterval(interval);
            isMonitored = false;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch("{{ route('logout') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).finally(() => {
                window.location.href = '/';
            });
        }

        function updateLastActivity() {
            if (!alertActive && isMonitored) {
                localStorage.setItem('last_activity', Date.now().toString());
            }
        }

        function checkInactivity() {
            const lastActivityStr = localStorage.getItem('last_activity');
            if (!lastActivityStr) return;
            const diff = Date.now() - parseInt(lastActivityStr);
            if (diff >= INACTIVITY_TIME) {
                logout();
            }
            else if (diff >= WARNING_THRESHOLD && !alertActive) {
                alertActive = true;
                let timerInterval;
                Swal.fire({
                    title: '¿Sigues ahi?',
                    html: 'Tu sesión se cerrará por inactividad en <br><b></b> segundos.',
                    timer: WARNING_SECONDS * 1000,
                    timerProgressBar: true,
                    confirmButtonText: 'Seguir conectado',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'custom-swal-container',
                        title: 'custom-swal-title',
                        htmlContainer: 'custom-swal-html',
                        confirmButton: 'custom-confirm-button'
                    },
                    didOpen: () => {
                        const b = Swal.getHtmlContainer().querySelector('b');
                        timerInterval = setInterval(() => {
                            b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        alertActive = false;
                        updateLastActivity();
                        fetch('/dashboard').catch(() => {});
                    } else if (result.dismiss === Swal.DismissReason.timer) {
                        logout();
                    } else {
                        logout();
                    }
                });
            }
        }

        function startMonitoring(user) {
            if (user && !isMonitored) {
                isMonitored = true;
                localStorage.setItem('last_activity', Date.now().toString());
                if (interval) clearInterval(interval);
                interval = setInterval(checkInactivity, 2000);
                const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
                events.forEach(e => {
                    window.removeEventListener(e, updateLastActivity, true);
                    window.addEventListener(e, updateLastActivity, true);
                });
            } else if (!user && isMonitored) {
                isMonitored = false;
                if (interval) clearInterval(interval);
                localStorage.removeItem('last_activity');
            }
        }

        document.addEventListener('inertia:success', (event) => {
            const user = event.detail.page.props.auth?.user;
            startMonitoring(user);
        });

        window.addEventListener('load', () => {
            try {
                const pageData = JSON.parse(document.getElementById('app').dataset.page);
                const user = pageData.props.auth?.user;
                startMonitoring(user);
            } catch (e) {
                console.error("Error al leer datos iniciales");
            }
        });
    })();
</script>

</body>

</html>
