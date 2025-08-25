// Fichier JavaScript minimaliste pour InfluBoss
document.addEventListener('DOMContentLoaded', function() {
    console.log('InfluBoss JS chargé avec succès');
    
    // Initialisation des composants dynamiques de l'interface
    initFormControls();
});

// Initialisation des contrôles de formulaires
function initFormControls() {
    // Gestion des select2 si présents
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    }
    
    // Gestion des datepickers si présents
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'fr'
        });
    }
    
    // Gestion de la validation des formulaires
    const forms = document.querySelectorAll('.needs-validation');
    if (forms.length > 0) {
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }
}
