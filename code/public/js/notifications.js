document.addEventListener('DOMContentLoaded', function () {
    const notificationButton = document.getElementById('dropdown-notification');
    const notificationBadge = notificationButton.querySelector('span');
    const notificationsList = document.getElementById('header-notification-scroll');
    const markAllReadButton = document.getElementById('mark-all-read');

    // Funzione per aggiornare le notifiche nella view
    function updateNotifications(data) {
        console.log(data);
        // Aggiorna il badge
        if (data.count > 0) {
            notificationBadge.classList.remove('hidden');
            markAllReadButton.classList.remove('!hidden');
            notificationBadge.querySelector('.animate-slow-ping').style.display = 'inline-block';
        } else {
            notificationBadge.classList.add('hidden');
            markAllReadButton.classList.add('!hidden');
        }

        // Aggiorna la lista delle notifiche
        notificationsList.innerHTML = ''; // Pulisci la lista esistente

        if (data.notifications.length > 0) {
            data.notifications.forEach(notification => {
                // Determina l'icona basata sul tipo
                let iconClass = 'la la-file-alt';
                let bgClass = 'bg-pinkmain';
                switch (notification.type) {
                    case 'order':
                        iconClass = 'la la-shopping-basket';
                        bgClass = 'bg-pinkmain';
                        break;
                    case 'message':
                        iconClass = 'la la-envelope-open';
                        bgClass = 'bg-purplemain';
                        break;
                    case 'user':
                        iconClass = 'la la-user-check';
                        bgClass = 'bg-danger';
                        break;
                    case 'project':
                        iconClass = 'la la-check-circle';
                        bgClass = 'bg-primary';
                        break;
                    default:
                        iconClass = 'la la-file-alt';
                        bgClass = 'bg-pinkmain';
                }

                const li = document.createElement('li');
                li.classList.add('dropdown-item', 'px-3');

                li.innerHTML = `
                    <div class="flex items-center">
                        <span class="avatar avatar-md me-2 avatar-rounded flex-shrink-0 ${bgClass}">
                            <i class="${iconClass} text-[1.25rem]"></i>
                        </span>
                        <div class="ms-3">
                            <a href="${notification.url}">
                                <h5 class="notification-label text-defaulttextcolor mb-1">${notification.title}</h5>
                            </a>
                            <div class="notification-subtext">${notification.created_at}</div>
                        </div>
                        <div class="ms-auto">
                            <a href="${notification.url}"><i class="las la-angle-right text-end text-muted icon rtl:rotate-180"></i></a>
                        </div>
                    </div>
                `;

                notificationsList.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.classList.add('dropdown-item', 'px-3');
            li.innerHTML = `
                <div class="flex items-center">
                    <div class="ms-3">
                        <h5 class="notification-label text-defaulttextcolor mb-1">Non ci sono nuove notifiche.</h5>
                    </div>
                </div>
            `;
            notificationsList.appendChild(li);
        }
    }

    // Funzione per effettuare il polling
    function pollNotifications() {
        fetch('/api/notifications/unread', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include', // Invia i cookie di sessione
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nella richiesta delle notifiche.');
            }
            return response.json();
        })
        .then(data => {
            updateNotifications(data);
        })
        .catch(error => {
            console.error('Errore nel polling delle notifiche:', error);
        });
    }

    // Esegui il polling ogni 30 secondi
    //setInterval(pollNotifications, 30000); // 30000 ms = 30 secondi

    // Esegui il polling immediatamente al caricamento della pagina
    pollNotifications();

    // Gestisci "Mark All Read"
    markAllReadButton.addEventListener('click', function () {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Aggiorna la view delle notifiche
                updateNotifications({ count: 0, notifications: [] });
            }
        })
        .catch(error => {
            console.error('Errore nel segnare tutte le notifiche come lette:', error);
        });
    });
});
