const form = document.getElementById('guess-form');
const input = document.getElementById('guess-input');
const attemptsContainer = document.getElementById('attempts');
const resetButton = document.getElementById('reset-btn');
const successMessage = document.getElementById('success-message');

// Soumission d'une tentative
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const guess = input.value.trim();

    if (!guess) return;

    // Envoi de la tentative au backend
    const response = await fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom: guess })
    });

    const result = await response.json();

    // Créer le div pour la tentative
    const attemptDiv = document.createElement('div');
    attemptDiv.className = 'attempt';

    if (result.error) {
        attemptDiv.textContent = result.error;
    } else {
        // Affichage du nom du personnage avec une classe
        const nameElement = document.createElement('strong');
        nameElement.className = 'character-name';
        nameElement.textContent = guess;

        // Affichage des caractéristiques avec une classe
        const characteristicsList = document.createElement('ul');
        characteristicsList.className = 'characteristics-list';
        characteristicsList.innerHTML = Object.entries(result).map(([key, data]) => {
            if (key !== 'image' && key !== 'success') { // Ignore 'image' et 'success'
                return `
                    <li class="characteristic-item" style="color: ${data.match ? 'green' : 'red'}">
                        ${key}: ${data.value}
                    </li>`;
            }
        }).join('');

        // Ajout des éléments au conteneur
        attemptDiv.appendChild(nameElement);
        attemptDiv.appendChild(characteristicsList);

        // Affichage de l'image du personnage
        if (result.image) {
            const imgElement = document.createElement('img');
            imgElement.src = `/dle/images/${result.image}`;
            imgElement.alt = 'Image du personnage';
            imgElement.className = 'character-image'; // Classe CSS pour l'image
            attemptDiv.appendChild(imgElement);
        }

        // Vérification si toutes les caractéristiques sont correctes
        if (result.success) {
            // Supprimer les anciennes tentatives incorrectes
            attemptsContainer.innerHTML = ''; // Efface toutes les tentatives précédentes
            attemptsContainer.appendChild(attemptDiv); // Affiche uniquement la bonne tentative

            // Afficher le message de réussite
            successMessage.style.display = 'block';
            resetButton.style.display = 'inline-block';
        }
    }

    // Si ce n'est pas la bonne réponse, afficher cette tentative
    if (!result.success) {
        attemptsContainer.appendChild(attemptDiv);
    }

    input.value = '';
});

// Réinitialisation du jeu
resetButton.addEventListener('click', async () => {
    await fetch('index.php?reset=1');
    attemptsContainer.innerHTML = ''; // Efface toutes les tentatives
    successMessage.style.display = 'none'; // Masquer le message de succès
    resetButton.style.display = 'none'; // Masquer le bouton recommencer
    input.value = '';
    alert('Le jeu a été réinitialisé.');
});
