

// Fonction pour afficher la notification sur la page
function afficherNotification(event) {

    const notificationsDiv = document.getElementById('notifications');
    let notification = document.createElement('div');

    let additionalData = event.notification.additionalData;
    let code = (additionalData !== null && additionalData.code !== null && additionalData.code !== undefined) ? additionalData.code : null;
    console.log("Notification code affichée :", code);

    notification.className = "notification";

    // Construction du contenu de la notification en fonction du code
    if (code !== null) {
        if (code === "200") {
            notification.innerHTML = `
            <div class="alert alert-success" id="makuta-global-alert-message">
                <i class="fa fa-check-circle mr-3"></i>
                ${event.notification.body || 'Action réussie avec succès.'}
            </div>
        `;
        } else {
            notification.innerHTML = `
            <div class="alert alert-danger" id="makuta-global-alert-message">
                <i class="fa fa-times-circle mr-3"></i>
                ${event.notification.body || 'Une erreur est survenue.'}
            </div>
        `;
        }
    } else {
        notification.innerHTML = `
        <div class="alert alert-primary" id="makuta-global-alert-message">
            <i class="fa fa-info-circle mr-3"></i>
            ${event.notification.body || 'Vous avez reçu une nouvelle notification.'}
        </div>
    `;
    }

    // Ajouter la notification au conteneur
    notificationsDiv.appendChild(notification);
}


// Cibler toutes les alertes avec la classe `flash-message`
const flashMessages = document.querySelectorAll('.flash-message');

flashMessages.forEach((message) => {
    // Masquer et supprimer l'alerte après 6000 millisecondes
    setTimeout(() => {
        message.style.transition = "opacity 0.5s ease";
        message.style.opacity = "0"; // Animation de disparition
        setTimeout(() => message.remove(), 500); // Supprime du DOM après l'animation
    }, 6000);
});

