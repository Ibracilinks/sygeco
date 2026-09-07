// Remplace les `<select multiple>` (Ctrl/Cmd + clic, peu découvrable) par une liste à
// cases à cocher avec recherche et jetons — sans toucher au <select> natif, qui reste
// caché dans le DOM et continue de porter les valeurs soumises au serveur.
const construirePanneau = (select) => {
    const options = [...select.options];
    const placeholder = select.dataset.placeholder || 'Sélectionner…';

    const conteneur = document.createElement('div');
    conteneur.className = 'relative';

    const declencheur = document.createElement('button');
    declencheur.type = 'button';
    declencheur.className = 'flex min-h-[2.5rem] w-full flex-wrap items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-left text-sm dark:border-slate-700 dark:bg-slate-950';
    declencheur.setAttribute('aria-haspopup', 'listbox');
    declencheur.setAttribute('aria-expanded', 'false');

    const panneau = document.createElement('div');
    panneau.hidden = true;
    panneau.className = 'absolute z-20 mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-900';

    let champRecherche = null;
    if (options.length > 6) {
        champRecherche = document.createElement('input');
        champRecherche.type = 'text';
        champRecherche.placeholder = 'Rechercher…';
        champRecherche.className = 'mb-2 w-full rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
        panneau.appendChild(champRecherche);
    }

    const actions = document.createElement('div');
    actions.className = 'mb-2 flex items-center gap-3 text-xs';
    const boutonTout = document.createElement('button');
    boutonTout.type = 'button';
    boutonTout.textContent = 'Tout sélectionner';
    boutonTout.className = 'text-blue-600 hover:underline dark:text-blue-400';
    const boutonAucun = document.createElement('button');
    boutonAucun.type = 'button';
    boutonAucun.textContent = 'Tout désélectionner';
    boutonAucun.className = 'text-slate-500 hover:underline dark:text-slate-400';
    actions.append(boutonTout, boutonAucun);
    panneau.appendChild(actions);

    const liste = document.createElement('div');
    liste.className = 'max-h-56 space-y-0.5 overflow-y-auto';
    panneau.appendChild(liste);

    const lignes = options.map((option) => {
        const ligne = document.createElement('label');
        ligne.className = 'flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = option.selected;
        checkbox.className = 'h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 dark:border-slate-600';
        const texte = document.createElement('span');
        texte.textContent = option.textContent.trim();
        ligne.append(checkbox, texte);
        liste.appendChild(ligne);
        return { option, ligne, checkbox, texte };
    });

    const synchroniserDeclencheur = () => {
        const selectionnees = lignes.filter((l) => l.checkbox.checked);
        declencheur.innerHTML = '';
        if (selectionnees.length === 0) {
            const vide = document.createElement('span');
            vide.className = 'text-slate-400 dark:text-slate-500';
            vide.textContent = placeholder;
            declencheur.appendChild(vide);
            return;
        }
        selectionnees.forEach(({ option, checkbox }) => {
            const jeton = document.createElement('span');
            jeton.className = 'inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-200';
            jeton.textContent = option.textContent.trim();
            const retirer = document.createElement('span');
            retirer.textContent = '×';
            retirer.className = 'ml-0.5 cursor-pointer font-bold hover:text-blue-900 dark:hover:text-blue-50';
            retirer.title = 'Retirer';
            retirer.addEventListener('click', (evenement) => {
                evenement.stopPropagation();
                checkbox.checked = false;
                appliquer();
            });
            jeton.appendChild(retirer);
            declencheur.appendChild(jeton);
        });
    };

    const appliquer = () => {
        lignes.forEach(({ option, checkbox }) => { option.selected = checkbox.checked; });
        select.dispatchEvent(new Event('change', { bubbles: true }));
        synchroniserDeclencheur();
    };

    lignes.forEach(({ checkbox }) => checkbox.addEventListener('change', appliquer));

    boutonTout.addEventListener('click', () => {
        lignes.forEach(({ ligne, checkbox }) => { if (!ligne.hidden) checkbox.checked = true; });
        appliquer();
    });
    boutonAucun.addEventListener('click', () => {
        lignes.forEach(({ checkbox }) => { checkbox.checked = false; });
        appliquer();
    });

    if (champRecherche) {
        champRecherche.addEventListener('input', () => {
            const terme = champRecherche.value.trim().toLowerCase();
            lignes.forEach(({ ligne, texte }) => {
                ligne.hidden = terme.length > 0 && !texte.textContent.toLowerCase().includes(terme);
            });
        });
    }

    const fermer = () => {
        panneau.hidden = true;
        declencheur.setAttribute('aria-expanded', 'false');
    };
    const ouvrir = () => {
        panneau.hidden = false;
        declencheur.setAttribute('aria-expanded', 'true');
        champRecherche?.focus();
    };

    declencheur.addEventListener('click', () => (panneau.hidden ? ouvrir() : fermer()));
    document.addEventListener('click', (evenement) => {
        if (!conteneur.contains(evenement.target)) fermer();
    });
    conteneur.addEventListener('keydown', (evenement) => {
        if (evenement.key === 'Escape') {
            fermer();
            declencheur.focus();
        }
    });

    conteneur.append(declencheur, panneau);
    synchroniserDeclencheur();
    return conteneur;
};

const activer = (select) => {
    if (select.dataset.enhanced === '1') return;
    select.dataset.enhanced = '1';
    select.classList.add('hidden');
    select.setAttribute('aria-hidden', 'true');
    select.tabIndex = -1;
    select.insertAdjacentElement('afterend', construirePanneau(select));
};

const demarrer = () => {
    document.querySelectorAll('select[multiple][data-ux-enhance]').forEach(activer);
};

document.addEventListener('DOMContentLoaded', demarrer);
document.addEventListener('livewire:navigated', demarrer);
