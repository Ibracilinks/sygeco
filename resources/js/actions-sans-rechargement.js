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

/** Indicateur « Envoi en cours… » au centre de l'écran, pendant que la liste est floutée. */
const afficherChargement = () => {
    const indicateur = document.createElement('div');
    indicateur.className = 'chargement-action';
    indicateur.setAttribute('role', 'status');
    indicateur.setAttribute('aria-live', 'polite');
    indicateur.innerHTML = '<div class="chargement-action__boite"><span class="chargement-action__roue" aria-hidden="true"></span><span>Envoi en cours…</span></div>';
    document.body.appendChild(indicateur);

    return () => indicateur.remove();
};

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
    // Les fenêtres modales (hors zone) se referment dès l'envoi.
    document.querySelectorAll('[id$="Modal"]').forEach((modale) => modale.classList.add('hidden'));
    zone.setAttribute('aria-busy', 'true');
    const masquerChargement = afficherChargement();

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

        zone.replaceWith(document.importNode(nouvelleZone, true));
        document.dispatchEvent(new CustomEvent('zone:actualisee'));
    } catch (erreur) {
        envoyerNormalement(formulaire);
    } finally {
        masquerChargement();
        boutons.forEach((bouton) => { bouton.disabled = false; });
        zone.removeAttribute('aria-busy');
    }
});
