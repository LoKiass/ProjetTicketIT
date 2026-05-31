document.addEventListener('DOMContentLoaded', function () {
    const dateDebut = document.getElementById('Dstart');
    const dateCloture = document.getElementById('DClotEst');
    const dateDech = document.getElementById('Dech');

    function checkValidDate() {
        let hasError = false;

        if (dateDebut && dateDebut.value) {
            if (dateDech) dateDech.min = dateDebut.value;
            if (dateCloture && !dateDech) dateCloture.min = dateDebut.value;
        }
        if (dateDech && dateDech.value && dateCloture) {
            dateCloture.min = dateDech.value;
        }

        // Vérification Début vs Échéance
        if (dateDebut && dateDech && dateDebut.value && dateDech.value) {
            if (dateDech.value < dateDebut.value) {
                dateDech.classList.add('is-invalid');
                hasError = true;
            } else {
                dateDech.classList.remove('is-invalid');
            }
        }

        // Vérification Clôture vs Échéance / Début
        if (dateCloture && dateCloture.value) {
            let referenceValue = null;
            if (dateDech && dateDech.value) {
                referenceValue = dateDech.value;
            } else if (dateDebut && dateDebut.value) {
                referenceValue = dateDebut.value;
            }

            if (referenceValue && dateCloture.value < referenceValue) {
                dateCloture.classList.add('is-invalid');
                hasError = true;
            } else {
                dateCloture.classList.remove('is-invalid');
            }
        }

        return hasError;
    }

    checkValidDate(); // Appel de la fonction

    // Ajout des events pour les champs de date
    if (dateDebut) {
        dateDebut.addEventListener('change', checkValidDate);
    }
    if (dateDech) {
        dateDech.addEventListener('change', checkValidDate);
    }
    if (dateCloture) {
        dateCloture.addEventListener('change', checkValidDate);
    }

    // Modifications de la target du formulaire en fonction de ou nous l'avont declencher
    const targetForm = dateDebut ? dateDebut.closest('form') : null;
    if (targetForm) {
        targetForm.addEventListener('submit', function (event) {
            const erreurDetectee = validerEtBriderlesDates();
            if (erreurDetectee) {
                event.preventDefault();
            }
        });
    }
})