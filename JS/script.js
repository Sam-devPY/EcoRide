document.addEventListener('DOMContentLoaded', function () {
    const roleField = document.querySelector('#role');
    const specificFields = document.querySelector('#specific-fields');

    function toggleSpecificFields() {
        if (roleField.value === 'passager') {
            specificFields.style.display = 'none';
            specificFields.querySelectorAll('input, textarea, select').forEach(field => {
                field.disabled = true;
            });
        } else {
            specificFields.style.display = 'block';
            specificFields.querySelectorAll('input, textarea, select').forEach(field => {
                field.disabled = false;
            });
        }
    }

    toggleSpecificFields();
    roleField.addEventListener('change', toggleSpecificFields);
});

document.addEventListener('DOMContentLoaded', function () {
    const fields = document.querySelectorAll('[data-field]'); // Sélectionne tous les champs avec l'attribut data-field

    // Fonction pour envoyer les données au serveur
    function updateField(field, value) {
        const formData = new FormData();
        formData.append('field', field);
        formData.append('value', value);

        fetch('update_profil.php', { // Appel au fichier PHP
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Champ ${field} mis à jour avec succès.`);
            } else {
                console.error(`Erreur lors de la mise à jour du champ ${field}:`, data.error);
            }
        })
        .catch(error => console.error('Erreur réseau :', error));
    }

    // Ajoute un écouteur d'événement à chaque champ
    fields.forEach(field => {
        field.addEventListener('change', function () {
            const fieldName = this.getAttribute('data-field');
            const fieldValue = this.type === 'checkbox' ? (this.checked ? 1 : 0) : this.value;
            updateField(fieldName, fieldValue);
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const updateButton = document.querySelector('#update-vehicle');
    const specificFields = document.querySelectorAll('#specific-fields input, #specific-fields select');

    // Fonction pour envoyer les données des champs spécifiques au serveur
    function updateVehicle() {
        const formData = new FormData();

        specificFields.forEach(field => {
            const fieldName = field.getAttribute('name');
            const fieldValue = field.type === 'checkbox' ? (field.checked ? 1 : 0) : field.value;
            formData.append(fieldName, fieldValue);
        });

        fetch('update_vehicle.php', { // Appel au fichier PHP pour insérer les données
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Les informations du véhicule ont été mises à jour avec succès.');
            } else {
                alert('Erreur lors de la mise à jour : ' + data.error);
            }
        })
        .catch(error => console.error('Erreur réseau :', error));
    }

    // Ajoute un événement au clic sur le bouton "Actualiser"
    updateButton.addEventListener('click', updateVehicle);
});

document.addEventListener('DOMContentLoaded', function () {
    const photoInput = document.querySelector('#photo');
    const profilePhoto = document.querySelector('.profile-photo');

    // Fonction pour gérer l'upload de la photo
    function uploadPhoto(file) {
        const formData = new FormData();
        formData.append('photo', file);

        fetch('upload_photo.php', { // Appel au fichier PHP pour gérer l'upload
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Met à jour l'image affichée avec la nouvelle photo
                profilePhoto.src = `uploads/${data.fileName}?t=${new Date().getTime()}`; // Ajout d'un timestamp pour éviter le cache
                alert('Photo de profil mise à jour avec succès.');
            } else {
                alert('Erreur lors de la mise à jour de la photo : ' + data.error);
            }
        })
        .catch(error => console.error('Erreur réseau :', error));
    }

    // Écouteur d'événement pour détecter le changement de fichier
    photoInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            uploadPhoto(this.files[0]);
        }
    });
});