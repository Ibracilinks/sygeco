/**
 * Actions sans rechargement de page (soumission, arbitrage).
 *
 * Un formulaire marqué `data-sans-rechargement` est envoyé en arrière-plan ; le
 * serveur répond par une redirection vers la page d'origine (filtres compris),
 * dont on ne reprend que la zone `[data-zone-actualisable]`. Filtres, pagination
 * et position de défilement restent en place. En cas d'échec, le formulaire est
 * envoyé normalement.
 */
const SELECTEUR_ZONE = '[data-zone-actualisable]';

const envoyerNormalement = (formulaire) => HTMLFormElement.prototype.submit.call(formulaire);

document.addEventListener('submit', async (evenement) => {
    const formulaire = evenement.target;

    if (!(formulaire instanceof HTMLFormElement) || !formulaire.hasAttribute('data-sans-rechargement')) {
        return;
    }

    // Confirmation refusée (onsubmit="return confirm(…)") : rien à faire.
    const zone = document.querySelector(SELECTEUR_ZONE);
    if (evenement.defaultPrevented || !zone) {
        return;
    }

    evenement.preventDefault();

    const boutons = formulaire.querySelectorAll('button, [type="submit"]');
    boutons.forEach((bouton) => { bouton.disabled = true; });
    zone.setAttribute('aria-busy', 'true');

    try {
        const reponse = await fetch(formulaire.action, {
            method: 'POST',
            body: new FormData(formulaire),
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            credentials: 'same-origin',
        });

        if (!reponse.ok) {
            throw new Error(`HTTP ${reponse.status}`);
        }

        // Redirigé vers une autre page (ex. fiche supprimée) : on y va réellement.
        const destination = new URL(reponse.url);
        if (destination.pathname !== window.location.pathname) {
            window.location.assign(reponse.url);
            return;
        }

        const page = new DOMParser().parseFromString(await reponse.text(), 'text/html');
        const nouvelleZone = page.querySelector(SELECTEUR_ZONE);

        if (!nouvelleZone) {
            throw new Error('Zone introuvable');
        }

        // Les fenêtres modales sont hors zone : on referme celles restées ouvertes.
        document.querySelectorAll('[id$="Modal"]').forEach((modale) => modale.classList.add('hidden'));

        zone.replaceWith(document.importNode(nouvelleZone, true));
        document.dispatchEvent(new CustomEvent('zone:actualisee'));
    } catch (erreur) {
        envoyerNormalement(formulaire);
    } finally {
        boutons.forEach((bouton) => { bouton.disabled = false; });
        zone.removeAttribute('aria-busy');
    }
});
