/**
 * Scripts pour les graphiques du tableau de bord
 */
document.addEventListener('DOMContentLoaded', function() {
    // Si les éléments canvas n'existent pas, ne pas continuer
    if (!document.getElementById('campaignStatusChart') || 
        !document.getElementById('monthlyPublicationsChart') || 
        !document.getElementById('interactionStatsChart') || 
        !document.getElementById('platformStatsChart')) {
        return;
    }
    
    // Récupérer les données transmises par le backend
    const dashboardData = window.dashboardData || {};
    const campaignsByStatus = dashboardData.campaignsByStatus || {};
    const monthlyPublications = dashboardData.monthlyPublications || {};
    const interactionStats = dashboardData.interactionStats || {};
    const platformStats = dashboardData.platformStats || {};
    
    // Couleurs pour les graphiques
    const colors = {
        orange: '#FE9700', // Couleur principale Orange
        blue: '#1c84ee',
        green: '#28a745',
        red: '#dc3545',
        yellow: '#ffc107',
        grey: '#6c757d',
        purple: '#6f42c1',
        indigo: '#6610f2',
        teal: '#20c997',
        pink: '#e83e8c',
        cyan: '#17a2b8'
    };
    
    //---------------------
    // Graphique des campagnes par statut
    //---------------------
    const campaignStatusLabels = Object.keys(campaignsByStatus).map(status => {
        // Première lettre en majuscule
        return status.charAt(0).toUpperCase() + status.slice(1);
    });
    
    const campaignStatusColors = {
        'draft': colors.grey,
        'active': colors.green,
        'paused': colors.yellow,
        'completed': colors.blue
    };
    
    new Chart(document.getElementById('campaignStatusChart'), {
        type: 'doughnut',
        data: {
            labels: campaignStatusLabels,
            datasets: [{
                data: Object.values(campaignsByStatus),
                backgroundColor: campaignStatusLabels.map(label => campaignStatusColors[label.toLowerCase()] || colors.orange)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: 'Répartition des campagnes par statut'
                }
            }
        }
    });
    
    //---------------------
    // Graphique des publications mensuelles
    //---------------------
    const monthlyLabels = Object.keys(monthlyPublications);
    
    new Chart(document.getElementById('monthlyPublicationsChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Nombre de publications',
                data: Object.values(monthlyPublications),
                borderColor: colors.orange,
                backgroundColor: colors.orange + '40',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Évolution des publications par mois'
                }
            }
        }
    });
    
    //---------------------
    // Graphique des interactions
    //---------------------
    const interactionLabels = Object.keys(interactionStats);
    
    const interactionColors = {
        'likes': colors.blue,
        'comments': colors.green,
        'shares': colors.purple,
        'views': colors.orange,
        'clicks': colors.yellow
    };
    
    new Chart(document.getElementById('interactionStatsChart'), {
        type: 'bar',
        data: {
            labels: interactionLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
            datasets: [{
                label: 'Nombre',
                data: Object.values(interactionStats),
                backgroundColor: interactionLabels.map(label => interactionColors[label] || colors.grey)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Statistiques d\'engagement'
                }
            }
        }
    });
    
    //---------------------
    // Graphique des plateformes
    //---------------------
    const platformLabels = Object.keys(platformStats);
    
    const platformColors = {
        'instagram': '#E1306C',
        'facebook': '#4267B2',
        'twitter': '#1DA1F2',
        'tiktok': '#000000',
        'youtube': '#FF0000',
        'linkedin': '#0077B5'
    };
    
    new Chart(document.getElementById('platformStatsChart'), {
        type: 'pie',
        data: {
            labels: platformLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
            datasets: [{
                data: Object.values(platformStats),
                backgroundColor: platformLabels.map(label => platformColors[label] || colors.grey)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                title: {
                    display: true,
                    text: 'Publications par plateforme'
                }
            }
        }
    });
});
