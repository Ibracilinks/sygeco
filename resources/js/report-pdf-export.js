import { jsPDF } from 'jspdf';

window.jspdf = window.jspdf || { jsPDF };

/**
 * Réduit la police jusqu'à ce que le texte tienne dans la largeur disponible.
 * Sous la taille plancher, le texte est tronqué : rien ne dépasse d'une cellule.
 */
const ajusterTexte = (doc, valeur, largeur, taille, plancher = 4) => {
    let texte = String(valeur ?? '');
    const mesure = (contenu, size) => doc.getStringUnitWidth(contenu) * size / doc.internal.scaleFactor;

    while (taille > plancher && mesure(texte, taille) > largeur) {
        taille -= 0.25;
    }

    while (texte.length > 1 && mesure(texte, taille) > largeur) {
        texte = texte.slice(0, -2) + '…';
    }

    return { texte, taille };
};

/**
 * Cellule bordée dont le contenu ne sort jamais du cadre : une valeur simple est
 * mise à l'échelle, un tableau de lignes est réparti sur la hauteur.
 */
const cellule = (doc, x, y, w, h, valeur = '', options = {}) => {
    doc.rect(x, y, w, h);

    const align = options.align || 'left';
    const textX = align === 'right' ? x + w - 1.2 : align === 'center' ? x + w / 2 : x + 1.2;
    const tailleInitiale = doc.getFontSize();
    const largeurUtile = Math.max(2, w - 2.6);

    if (Array.isArray(valeur)) {
        const lignes = valeur.map((ligne) => ajusterTexte(doc, ligne, largeurUtile, tailleInitiale));
        const taille = Math.min(...lignes.map((ligne) => ligne.taille));
        const interligne = taille * 0.42;
        const departY = y + h / 2 - ((lignes.length - 1) * interligne) / 2 + interligne / 3;

        doc.setFontSize(taille);
        lignes.forEach((ligne, index) => doc.text(ligne.texte, textX, departY + index * interligne, { align }));
        doc.setFontSize(tailleInitiale);

        return;
    }

    const { texte, taille } = ajusterTexte(doc, valeur, largeurUtile, tailleInitiale);
    doc.setFontSize(taille);
    doc.text(texte, textX, y + h / 2, { align, baseline: 'middle' });
    doc.setFontSize(tailleInitiale);
};

/** Ligne de texte centrée, réduite si besoin pour tenir dans la largeur donnée. */
const texteCentreAjuste = (doc, valeur, centreX, y, largeur, taille) => {
    const ajuste = ajusterTexte(doc, valeur, largeur, taille);
    doc.setFontSize(ajuste.taille);
    doc.text(ajuste.texte, centreX, y, { align: 'center' });
    doc.setFontSize(taille);

    return ajuste;
};

/**
 * Produit un A4 portrait tenant sur une seule page. `dessiner(doc, k)` trace le
 * document et renvoie l'ordonnée de fin ; tout ce qui suit l'en-tête (à partir de
 * `debutCorps`) doit être dimensionné en multiples de `k`. Un premier passage à
 * blanc (`brouillon` vrai) mesure la hauteur à l'échelle 1 ; si elle dépasse la
 * page, hauteurs de ligne, espacements et polices sont réduits d'un même facteur.
 */
const surUnePage = (dessiner, debutCorps) => {
    const nouveau = () => new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4', compress: true });
    const brouillon = nouveau();
    const fin = dessiner(brouillon, 1, true);
    const disponible = brouillon.internal.pageSize.getHeight() - MARGE_MISSION - debutCorps;
    const k = Math.min(1, disponible / Math.max(1, fin - debutCorps));

    const doc = nouveau();
    dessiner(doc, k, false);

    return doc;
};

const MARGE_MISSION = 10;
const DEBUT_CORPS_MISSION = 50;

/** Désactive le bouton et affiche « Génération… » le temps de `tache`. */
const avecBouton = async (button, tache) => {
    const originalLabel = button ? button.innerHTML : null;
    if (button) {
        button.disabled = true;
        button.dataset.loading = '1';
        button.innerHTML = 'Génération…';
    }

    try {
        await tache();
    } finally {
        if (button && originalLabel !== null) {
            button.disabled = false;
            delete button.dataset.loading;
            button.innerHTML = originalLabel;
        }
    }
};

/** « ARRETE A LA SOMME DE : » suivi du montant en lettres, centré. */
const sommeEnLettres = (doc, montantEnLettres, y, taille, largeurMax) => {
    const pageW = doc.internal.pageSize.getWidth();
    const prefixe = 'ARRETE A LA SOMME DE : ';
    const lettres = String(montantEnLettres || '');
    const plancher = taille * 5 / 8.5;
    const mesure = () => (doc.getStringUnitWidth(prefixe + lettres) * taille) / doc.internal.scaleFactor;

    doc.setFont('helvetica', 'bold');
    while (taille > plancher && mesure() > largeurMax) {
        taille -= 0.25;
    }
    doc.setFontSize(taille);
    const largeurPrefixe = doc.getStringUnitWidth(prefixe) * taille / doc.internal.scaleFactor;
    const largeurLettres = doc.getStringUnitWidth(lettres) * taille / doc.internal.scaleFactor;
    const depart = (pageW - (largeurPrefixe + largeurLettres)) / 2;

    doc.text(prefixe, depart, y);
    doc.text(lettres, depart + largeurPrefixe, y);
};

/**
 * Blocs de signature côte à côte : libellé, nom souligné `ecartNom` mm plus bas
 * (à l'échelle k), fonction en italique dessous.
 */
const signatures = (doc, signataires, y, k, margin, fullW, souligne, ecartNom) => {
    const liste = Array.isArray(signataires) ? signataires : [];
    const blockWidth = fullW / Math.max(1, liste.length || 1);

    liste.forEach((signataire, index) => {
        const centerX = margin + blockWidth * index + blockWidth / 2;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(8 * k);
        doc.text(doc.splitTextToSize(String(signataire.libelle || ''), blockWidth - 6), centerX, y, { align: 'center' });

        const nom = String(signataire.nom || '');
        const nomAjuste = texteCentreAjuste(doc, nom, centerX, y + ecartNom * k, blockWidth - 6, 9 * k);
        souligne(nomAjuste.texte, centerX, y + ecartNom * k, nomAjuste.taille);

        doc.setFont('helvetica', 'italic');
        doc.setFontSize(7.5 * k);
        doc.text(doc.splitTextToSize(String(signataire.fonction || ''), blockWidth - 6), centerX, y + (ecartNom + 5) * k, { align: 'center' });
    });
};


/**
 * Génère un PDF complet à partir d'une configuration JSON décrivant le rapport :
 * en-tête, KPIs, graphiques (capturés depuis les canvases Chart.js),
 * listes/tableaux, alertes et prévisions.
 *
 * La configuration est lue depuis un <script type="application/json"> dont l'id
 * est passé en second paramètre. Les graphiques sont retrouvés via l'id de leur
 * conteneur (`containerId`).
 */
window.exportReportPDF = async function exportReportPDF(button, dataElId) {
    const dataEl = document.getElementById(dataElId);
    if (!dataEl) {
        window.print();
        return;
    }

    const report = JSON.parse(dataEl.textContent);

    const originalLabel = button ? button.innerHTML : null;
    if (button) {
        button.disabled = true;
        button.dataset.loading = '1';
        button.innerHTML = 'Génération…';
    }

    try {
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4', compress: true });
        const pageW = doc.internal.pageSize.getWidth();
        const pageH = doc.internal.pageSize.getHeight();
        const margin = 14;
        const contentW = pageW - margin * 2;
        let y = margin;

        const ensureSpace = (needed) => {
            if (y + needed > pageH - margin) {
                doc.addPage();
                y = margin;
            }
        };

        // ---- En-tête ----
        doc.setFillColor(37, 99, 235);
        doc.rect(0, 0, pageW, 26, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text(report.title || 'Rapport', margin, 13);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        if (report.subtitle) {
            doc.text(report.subtitle, margin, 20);
        } else if (report.annee) {
            doc.text(`Exercice ${report.annee}`, margin, 20);
        }
        doc.text(`Généré le ${new Date().toLocaleString('fr-FR')}`, pageW - margin, 20, { align: 'right' });
        y = 34;
        doc.setTextColor(15, 23, 42);

        const sectionTitle = (label) => {
            ensureSpace(12);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(13);
            doc.setTextColor(30, 41, 59);
            doc.text(label, margin, y);
            y += 2;
            doc.setDrawColor(203, 213, 225);
            doc.line(margin, y, pageW - margin, y);
            y += 6;
            doc.setTextColor(15, 23, 42);
        };

        const renderList = (items, emptyText) => {
            if (!items || !items.length) {
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.setTextColor(71, 85, 105);
                ensureSpace(6);
                doc.text(emptyText || 'Aucune donnée', margin + 2, y);
                y += 6;
                return;
            }
            items.forEach((it) => {
                ensureSpace(6);
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.setTextColor(71, 85, 105);
                const label = doc.splitTextToSize(String(it.label ?? ''), contentW - 45)[0];
                doc.text(`• ${label}`, margin + 2, y);
                if (it.value !== undefined && it.value !== null && it.value !== '') {
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(30, 41, 59);
                    doc.text(String(it.value), pageW - margin, y, { align: 'right' });
                }
                y += 5;
            });
            y += 2;
        };

        // ---- KPIs ----
        if (Array.isArray(report.kpis) && report.kpis.length) {
            sectionTitle(report.kpis_title || "Vue d'ensemble");
            const cols = 2;
            const gap = 4;
            const cardW = (contentW - gap * (cols - 1)) / cols;
            const cardH = 18;
            report.kpis.forEach((kpi, i) => {
                const col = i % cols;
                if (col === 0) ensureSpace(cardH + gap);
                const x = margin + col * (cardW + gap);
                const cardY = y;
                doc.setFillColor(241, 245, 249);
                doc.roundedRect(x, cardY, cardW, cardH, 2, 2, 'F');
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.setTextColor(100, 116, 139);
                doc.text(kpi.label, x + 4, cardY + 6);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(13);
                doc.setTextColor(15, 23, 42);
                doc.text(kpi.value, x + 4, cardY + 14);
                if (col === cols - 1 || i === report.kpis.length - 1) {
                    y += cardH + gap;
                }
            });
            y += 2;
        }

        // ---- Graphiques ----
        // Les graphiques colorent leur texte (légende/axes) selon le thème au
        // moment du rendu : on choisit le fond de capture en conséquence pour
        // garantir la lisibilité dans le PDF.
        const isDark = document.documentElement.classList.contains('dark');
        const chartBg = isDark ? '#1e293b' : '#ffffff';
        const charts = Array.isArray(report.charts) ? report.charts : [];
        if (charts.length) {
            sectionTitle(report.charts_title || 'Graphiques');
        }
        for (const chart of charts) {
            const canvas = document.querySelector(`#${chart.containerId} canvas`);
            if (!canvas) continue;

            const tmp = document.createElement('canvas');
            tmp.width = canvas.width;
            tmp.height = canvas.height;
            const tctx = tmp.getContext('2d');
            tctx.fillStyle = chartBg;
            tctx.fillRect(0, 0, tmp.width, tmp.height);
            tctx.drawImage(canvas, 0, 0);
            const imgData = tmp.toDataURL('image/png', 1.0);

            const ratio = canvas.height / canvas.width;
            const imgW = contentW;
            const imgH = Math.min(imgW * ratio, 95);

            ensureSpace(8 + imgH + 6);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(30, 41, 59);
            doc.text(chart.title, margin, y);
            y += 4;
            doc.addImage(imgData, 'PNG', margin, y, imgW, imgH, undefined, 'FAST');
            y += imgH + 6;
        }

        // ---- Tableaux / listes ----
        if (Array.isArray(report.tables) && report.tables.length) {
            report.tables.forEach((table) => {
                sectionTitle(table.title);
                renderList(table.rows, table.empty);
            });
        }

        // ---- Alertes ----
        if (report.alerts) {
            sectionTitle(report.alerts_title || 'Alertes');
            const blocks = [
                { title: 'Dépassements Budgétaires', items: report.alerts.over_budget, empty: 'Aucun dépassement détecté' },
                { title: 'Budgets Sous-utilisés', items: report.alerts.under_utilized, empty: 'Aucune sous-utilisation détectée' },
                { title: 'Activités Coûteuses', items: report.alerts.high_cost, empty: 'Aucune activité coûteuse détectée' },
            ];
            blocks.forEach((block) => {
                ensureSpace(10);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.setTextColor(51, 65, 85);
                doc.text(block.title, margin, y);
                y += 5;
                renderList(block.items, block.empty);
            });
        }

        // ---- Prévisions ----
        if (Array.isArray(report.forecast) && report.forecast.length) {
            sectionTitle(report.forecast_title || 'Tendances et Prévisions');
            renderList(report.forecast);
        }

        // ---- Pied de page (numéros) ----
        const pages = doc.getNumberOfPages();
        const footer = report.footer || 'CANAM';
        for (let p = 1; p <= pages; p++) {
            doc.setPage(p);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(148, 163, 184);
            doc.text(`Page ${p} / ${pages}`, pageW - margin, pageH - 6, { align: 'right' });
            doc.text(footer, margin, pageH - 6);
        }

        doc.save((report.filename || 'rapport') + '.pdf');
    } catch (e) {
        console.error('Erreur export PDF', e);
        alert("Une erreur est survenue lors de la génération du PDF.");
    } finally {
        if (button && originalLabel !== null) {
            button.disabled = false;
            delete button.dataset.loading;
            button.innerHTML = originalLabel;
        }
    }
};

// Compat : ancien point d'entrée de la page d'analyse budgétaire.
window.exportBudgetAnalysisPDF = (button) => window.exportReportPDF(button, 'budget-report-data');

// ─── Missions ────────────────────────────────────────────────────────────────

// Regroupement manuel par espace ASCII classique : l'espace fine insécable
// que produit Intl.NumberFormat('fr-FR') n'existe pas dans les polices
// standard de jsPDF et s'affiche comme un caractère parasite (ex. "75/000").
const fmt = (value) => {
    const nombre = Math.round(Number(value || 0));
    const signe = nombre < 0 ? '-' : '';
    return signe + Math.abs(nombre).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
};

const largeurTexte = (doc, texte, taille) => doc.getStringUnitWidth(String(texte)) * taille / doc.internal.scaleFactor;

/** Texte centré en x, souligné à sa largeur réelle. */
const souligneCentre = (doc, texte, x, y, taille) => {
    const largeur = largeurTexte(doc, texte, taille);
    doc.line(x - largeur / 2, y + 1, x + largeur / 2, y + 1);
};

/** Texte aligné à gauche et souligné à sa largeur réelle. */
const texteSouligne = (doc, texte, x, y, taille) => {
    doc.text(texte, x, y);
    doc.line(x, y + 1, x + largeurTexte(doc, texte, taille), y + 1);
};

/**
 * Logo officiel ramené à ~300 dpi pour 16 mm et encodé en JPEG sur fond blanc :
 * l'original (1024 px, RGBA) pèse plus d'un mégaoctet dans chaque PDF.
 */
const chargerLogoMission = async () => {
    const img = document.getElementById('mission-logo');
    if (!img) return null;
    try {
        if (!img.complete) await img.decode();
        if (!img.naturalWidth) return null;

        const cote = 200;
        const canvas = document.createElement('canvas');
        canvas.width = cote;
        canvas.height = cote;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, cote, cote);
        ctx.drawImage(img, 0, 0, cote, cote);

        return canvas.toDataURL('image/jpeg', 0.9);
    } catch (e) {
        return null;
    }
};

/** En-tête officiel (ministère / république, logo), à taille fixe. */
const enteteOfficiel = (doc, logo) => {
    const margin = MARGE_MISSION;
    const colonne = (doc.internal.pageSize.getWidth() - margin * 2) / 2;
    const centreGauche = margin + colonne / 2;
    const centreDroite = margin + colonne + colonne / 2;

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8);
    doc.text(doc.splitTextToSize('MINISTERE DE LA SANTE ET DU DEVELOPPEMENT SOCIAL', colonne - 6), centreGauche, 14, { align: 'center' });
    doc.text('------------------------', centreGauche, 21, { align: 'center' });
    doc.text("CAISSE NATIONALE D'ASSURANCE MALADIE", centreGauche, 25, { align: 'center' });

    doc.text('REPUBLIQUE DU MALI', centreDroite, 14, { align: 'center' });
    doc.text('----------------------', centreDroite, 18, { align: 'center' });
    doc.text('UN PEUPLE – UN BUT – UNE FOI', centreDroite, 22, { align: 'center' });

    if (logo) {
        doc.addImage(logo, 'JPEG', centreGauche - 8, 27, 16, 16, 'logo-canam', 'FAST');
    }
};

/** Titre suivi de la référence, centré et souligné, réduit s'il dépasse la largeur. */
const titreReference = (doc, titre, reference, y, taille, plancher) => {
    const pageW = doc.internal.pageSize.getWidth();
    const fullW = pageW - MARGE_MISSION * 2;
    const ref = String(reference || '');

    while (taille > plancher && largeurTexte(doc, titre + ref, taille) > fullW) {
        taille -= 0.25;
    }
    doc.setFontSize(taille);
    const largeurTitre = largeurTexte(doc, titre, taille);
    const depart = (pageW - (largeurTitre + largeurTexte(doc, ref, taille))) / 2;

    doc.text(titre, depart, y);
    doc.text(ref, depart + largeurTitre, y);
    doc.line(depart, y + 1, depart + largeurTitre + largeurTexte(doc, ref, taille), y + 1);
};

/**
 * « OBJET DE LA MISSION » souligné ligne à ligne, aligné à gauche ou centré.
 * Renvoie l'ordonnée sous le bloc.
 */
const objetMission = (doc, libelle, objet, y, k, centre = false) => {
    const pageW = doc.internal.pageSize.getWidth();
    const taille = 9 * k;
    const interligne = 4.2 * k;

    doc.setFontSize(taille);
    const lignes = doc.splitTextToSize(`${libelle} ${String(objet || '').toUpperCase()}`, pageW - MARGE_MISSION * 2);
    lignes.forEach((ligne, index) => {
        const ligneY = y + index * interligne;
        if (centre) {
            doc.text(ligne, pageW / 2, ligneY, { align: 'center' });
            souligneCentre(doc, ligne, pageW / 2, ligneY, taille);
        } else {
            texteSouligne(doc, ligne, MARGE_MISSION, ligneY, taille);
        }
    });

    return y + lignes.length * interligne;
};

/** Abscisses de départ des colonnes d'un tableau commençant à la marge. */
const abscisses = (largeurs) => {
    let curseur = MARGE_MISSION;
    return largeurs.map((largeur) => {
        const x = curseur;
        curseur += largeur;
        return x;
    });
};

/** Montant en lettres, lieu, date et signatures : bas commun aux trois modèles. */
const piedMission = (doc, mission, y, k, { lettres, ecartNom }) => {
    const pageW = doc.internal.pageSize.getWidth();
    const fullW = pageW - MARGE_MISSION * 2;

    lettres(y);
    y += 10 * k;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9 * k);
    doc.text(`${mission.lieu_signature} le ....................`, pageW - MARGE_MISSION, y, { align: 'right' });
    y += 12 * k;

    signatures(doc, mission.signataires, y, k, MARGE_MISSION, fullW,
        (texte, x, ligneY, taille) => souligneCentre(doc, texte, x, ligneY, taille), ecartNom);

    return y;
};

/**
 * Point d'entrée commun : lit les données, charge le logo une seule fois,
 * dessine l'en-tête fixe puis le corps (`dessinerCorps(doc, k, mission)`,
 * qui renvoie l'ordonnée de fin) sur une page A4 portrait, et télécharge.
 */
const exporterMission = async (button, dataElId, nomParDefaut, dessinerCorps) => {
    const dataEl = document.getElementById(dataElId);
    if (!dataEl) {
        window.print();
        return;
    }

    const mission = JSON.parse(dataEl.textContent);

    await avecBouton(button, async () => {
        try {
            const logo = await chargerLogoMission();
            const doc = surUnePage((page, k, brouillon) => {
                // La passe de mesure n'a pas besoin du logo : on évite de l'encoder deux fois.
                enteteOfficiel(page, brouillon ? null : logo);
                return dessinerCorps(page, k, mission);
            }, DEBUT_CORPS_MISSION);

            doc.save(`mission-${String(mission.reference || nomParDefaut).replace(/[^\w-]+/g, '_')}.pdf`);
        } catch (e) {
            console.error('Erreur export PDF mission', e);
            alert("Une erreur est survenue lors de la génération du PDF.");
        }
    });
};

window.exportMissionMemeVillePDF = (button, dataElId) => exporterMission(button, dataElId, 'meme-ville', (doc, k, mission) => {
    const r = (valeur) => valeur * k;
    const pageW = doc.internal.pageSize.getWidth();
    const margin = MARGE_MISSION;
    const fullW = pageW - margin * 2;
    const drawCell = (x, y, w, h, value = '', options = {}) => cellule(doc, x, y, w, h, value, options);

    // ── Titre ───────────────────────────────────────────────────────────
    let y = DEBUT_CORPS_MISSION;
    titreReference(doc, "BUDGET RELATIF A L'ORDRE DE MISSION N°", mission.reference, y, r(12), r(7));

    // ── Objet et durée ──────────────────────────────────────────────────
    y = objetMission(doc, 'OBJET DE LA MISSION:', mission.objet, y + r(8), k) + r(6);
    texteSouligne(doc, 'DUREE :', margin, y, r(9));
    doc.text(`${mission.nombre_jours} jour(s) ouvrable(s)`, margin + r(18), y);
    doc.setFont('helvetica', 'normal');
    doc.text(`${mission.date_depart} au ${mission.date_retour}`, margin, y + r(5));

    // ── Tableau (mêmes colonnes que le modèle officiel) ──────────────────
    y += r(11);
    const colWidths = [9, 46, 13, 15, 13, 16, 17, 15, 19, 27];
    const colX = abscisses(colWidths);
    const largeurTable = colWidths.reduce((somme, largeur) => somme + largeur, 0);
    const derniere = colWidths.length - 1;

    doc.setFontSize(r(6.5));
    doc.setFont('helvetica', 'bold');
    ['N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
        'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'TOTAL',
    ].forEach((entete, index) => drawCell(colX[index], y, colWidths[index], r(9), entete.split('\n'), { align: 'center' }));
    y += r(9);

    drawCell(margin, y, largeurTable, r(5), 'I-  FRAIS ET INDEMNITES', { align: 'center' });
    y += r(5);
    drawCell(colX[0], y, colWidths[0], r(5), '');
    drawCell(colX[1], y, colWidths[1], r(5), 'PRENOMS ET NOMS', { align: 'center' });
    drawCell(colX[2], y, largeurTable - colWidths[0] - colWidths[1], r(5), '');
    y += r(5);

    doc.setFontSize(r(7));
    mission.participants.forEach((participant, index) => {
        doc.setFont('helvetica', 'normal');
        drawCell(colX[0], y, colWidths[0], r(5), index + 1, { align: 'center' });
        doc.setFont('helvetica', 'italic');
        drawCell(colX[1], y, colWidths[1], r(5), participant.nom);
        doc.setFont('helvetica', 'bold');
        drawCell(colX[2], y, colWidths[2], r(5), '1', { align: 'center' });
        drawCell(colX[3], y, colWidths[3], r(5), '');
        drawCell(colX[4], y, colWidths[4], r(5), mission.nombre_jours, { align: 'center' });
        for (let colonne = 5; colonne <= derniere; colonne++) {
            drawCell(colX[colonne], y, colWidths[colonne], r(5), '');
        }
        y += r(5);
    });

    doc.setFont('helvetica', 'bold');
    drawCell(margin, y, largeurTable - colWidths[derniere], r(5), 'SOUS-TOTAL 1', { align: 'center' });
    drawCell(colX[derniere], y, colWidths[derniere], r(5), '-', { align: 'right' });
    y += r(5);

    drawCell(margin, y, largeurTable, r(5), 'II-  CARBURANT', { align: 'center' });
    y += r(5);
    drawCell(margin, y, largeurTable / 2, r(5), 'NBRE DE JOURS OUVRABLE', { align: 'center' });
    drawCell(margin + largeurTable / 2, y, largeurTable / 2, r(5), 'NOMBRE DE TICKET PAR JOUR', { align: 'center' });
    y += r(5);

    const largeurRestante = largeurTable - colWidths[derniere];
    drawCell(margin, y, largeurRestante / 3, r(5), 'TICKETS DE CARBURANT');
    doc.setFont('helvetica', 'normal');
    drawCell(margin + largeurRestante / 3, y, largeurRestante / 3, r(5), mission.nombre_jours, { align: 'center' });
    drawCell(margin + (largeurRestante * 2) / 3, y, largeurRestante / 3, r(5), mission.tickets_par_jour, { align: 'center' });
    doc.setFont('helvetica', 'bold');
    drawCell(colX[derniere], y, colWidths[derniere], r(5), mission.nombre_tickets, { align: 'center' });
    y += r(5);

    drawCell(margin, y, largeurRestante, r(5), 'SOUS-TOTAL 2', { align: 'center' });
    drawCell(colX[derniere], y, colWidths[derniere], r(5), mission.nombre_tickets, { align: 'center' });
    y += r(5);
    doc.setFontSize(r(9));
    drawCell(margin, y, largeurRestante, r(6), 'TOTAL', { align: 'center' });
    drawCell(colX[derniere], y, colWidths[derniere], r(6), mission.nombre_tickets, { align: 'center' });
    y += r(12);

    // ── Total en lettres, lieu et signatures ────────────────────────────
    y = piedMission(doc, mission, y, k, {
        lettres: (ligneY) => {
            doc.setFontSize(r(9));
            texteCentreAjuste(doc, mission.tickets_en_lettres || '', pageW / 2, ligneY, fullW, r(9));
        },
        ecartNom: 28,
    });

    return y + r(40);
});

window.exportMissionExterieurePDF = (button, dataElId) => exporterMission(button, dataElId, 'exterieure', (doc, k, mission) => {
    const r = (valeur) => valeur * k;
    const pageW = doc.internal.pageSize.getWidth();
    const margin = MARGE_MISSION;
    const fullW = pageW - margin * 2;
    const drawCell = (x, y, w, h, value = '', options = {}) => cellule(doc, x, y, w, h, value, options);

    // ── Titre et référence ──────────────────────────────────────────────
    let y = DEBUT_CORPS_MISSION;
    titreReference(doc, "PROJET DE BUDGET RELATIF A LA LEVEE D'ORDRE DE MISSION N°", mission.reference, y, r(11), r(7));

    // ── Objet et durée ──────────────────────────────────────────────────
    y = objetMission(doc, 'OBJET DE LA MISSION :', mission.objet, y + r(8), k, true) + r(6);
    texteSouligne(doc, 'DUREE MISSION :', margin, y, r(9));
    doc.text(`${mission.nombre_jours} jour(s)`, margin + r(32), y);
    doc.text(String(mission.destination || ''), pageW - margin, y, { align: 'right' });

    doc.setFont('helvetica', 'normal');
    doc.text(`DUREE : Du ${mission.date_depart} au ${mission.date_retour}`, margin, y + r(5));
    doc.setFontSize(r(8));
    doc.text(String(mission.zone_label || ''), pageW - margin, y + r(5), { align: 'right' });

    // ── Tableau (12 colonnes du modèle officiel, réparties sur 190 mm) ──
    y += r(11);
    const widths = [7, 38, 9, 15, 8, 17, 15, 9, 17, 18, 19, 18];
    const xs = abscisses(widths);
    const largeurTable = widths.reduce((somme, largeur) => somme + largeur, 0);
    const derniere = widths.length - 1;

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(r(6));
    [
        'N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
        'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'SOUS\nTOTAL',
        `Majoration\nzone ${Number(mission.zone_taux || 0)}%`, 'TOTAL\nGENERAL',
    ].forEach((entete, index) => drawCell(xs[index], y, widths[index], r(9), entete.split('\n'), { align: 'center' }));
    y += r(9);

    doc.setFontSize(r(7.5));
    drawCell(margin, y, largeurTable, r(5), 'I-  FRAIS ET INDEMNITES', { align: 'center' });
    y += r(5);
    drawCell(xs[0], y, widths[0], r(5), '');
    drawCell(xs[1], y, widths[1], r(5), 'PRENOMS ET NOMS', { align: 'center' });
    drawCell(xs[2], y, largeurTable - widths[0] - widths[1], r(5), '');
    y += r(5);

    doc.setFontSize(r(6.5));
    mission.participants.forEach((participant, index) => {
        const hauteur = r(6);
        const jours = Number(mission.nombre_jours || 0);
        doc.setFont('helvetica', 'normal');
        drawCell(xs[0], y, widths[0], hauteur, index + 1, { align: 'center' });
        doc.setFont('helvetica', 'italic');
        drawCell(xs[1], y, widths[1], hauteur, participant.nom);
        doc.setFont('helvetica', 'normal');
        drawCell(xs[2], y, widths[2], hauteur, '1', { align: 'center' });
        drawCell(xs[3], y, widths[3], hauteur, fmt(participant.montant_par_jour), { align: 'right' });
        drawCell(xs[4], y, widths[4], hauteur, mission.nombre_jours, { align: 'center' });
        drawCell(xs[5], y, widths[5], hauteur, fmt(Number(participant.montant_par_jour || 0) * jours), { align: 'right' });
        drawCell(xs[6], y, widths[6], hauteur, fmt(participant.montant_par_nuitee), { align: 'right' });
        drawCell(xs[7], y, widths[7], hauteur, participant.nombre_nuitees, { align: 'center' });
        drawCell(xs[8], y, widths[8], hauteur, fmt(Number(participant.montant_par_nuitee || 0) * Number(participant.nombre_nuitees || 0)), { align: 'right' });
        drawCell(xs[9], y, widths[9], hauteur, fmt(participant.sous_total), { align: 'right' });
        drawCell(xs[10], y, widths[10], hauteur, fmt(participant.majoration_montant), { align: 'right' });
        drawCell(xs[11], y, widths[11], hauteur, fmt(participant.total_general), { align: 'right' });
        y += hauteur;
    });

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(r(7.5));
    drawCell(margin, y, largeurTable - widths[derniere], r(5), 'SOUS TOTAL 1', { align: 'center' });
    drawCell(xs[derniere], y, widths[derniere], r(5), fmt(Number(mission.montant_indemnites || 0) + Number(mission.montant_majoration || 0)), { align: 'right' });
    y += r(5);

    // Sections II et III : mêmes colonnes fusionnées que le modèle.
    const colLibelle = widths[0] + widths[1];
    const colNombre = widths[2] + widths[3] + widths[4] + widths[5];
    const colUnitaire = widths[6] + widths[7] + widths[8];
    const colTotal = largeurTable - colLibelle - colNombre - colUnitaire;
    const xNombre = margin + colLibelle;
    const xUnitaire = xNombre + colNombre;
    const xTotal = xUnitaire + colUnitaire;

    const section = (titre, entetes, lignes, libelleSousTotal, sousTotal) => {
        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable, r(5), titre, { align: 'center' });
        y += r(5);
        drawCell(margin, y, colLibelle, r(5), '');
        drawCell(xNombre, y, colNombre, r(5), entetes[0], { align: 'center' });
        drawCell(xUnitaire, y, colUnitaire, r(5), entetes[1], { align: 'center' });
        drawCell(xTotal, y, colTotal, r(5), entetes[2], { align: 'center' });
        y += r(5);

        lignes.forEach(([libelle, nombre, unitaire, total]) => {
            doc.setFont('helvetica', 'bold');
            drawCell(margin, y, colLibelle, r(5), libelle);
            doc.setFont('helvetica', 'normal');
            drawCell(xNombre, y, colNombre, r(5), nombre, { align: 'center' });
            drawCell(xUnitaire, y, colUnitaire, r(5), fmt(unitaire), { align: 'right' });
            drawCell(xTotal, y, colTotal, r(5), fmt(total), { align: 'right' });
            y += r(5);
        });

        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable - colTotal, r(5), libelleSousTotal, { align: 'center' });
        drawCell(xTotal, y, colTotal, r(5), fmt(sousTotal), { align: 'right' });
        y += r(5);
    };

    section('II-  AUTRES FRAIS', ['NBRE DE PERSONNES', 'MONTANT PAR PERSONNE', 'MONTANT TOTAL'], [
        ['FRAIS DE PARTICIPATION', mission.frais_participation_nombre, mission.frais_participation_unitaire, mission.frais_participation_total],
        ['FRAIS DE VISA', mission.frais_visa_nombre, mission.frais_visa_unitaire, mission.frais_visa_total],
    ], 'SOUS TOTAL 2', mission.montant_autres_frais);

    section("III-  BILLETS D'AVION", ['NBRE DE PERSONNES', "PRIX D'UN BILLET", 'MONTANT TOTAL'], [
        ['CLASSE AFFAIRE', mission.billets_affaire_nombre, mission.billets_affaire_unitaire, mission.billets_affaire_total],
        ['CLASSE ECONOMIQUE', mission.billets_economique_nombre, mission.billets_economique_unitaire, mission.billets_economique_total],
    ], 'SOUS TOTAL 3', mission.montant_billets);

    doc.setFontSize(r(8));
    doc.setFont('helvetica', 'bold');
    drawCell(margin, y, largeurTable - colTotal, r(6), 'TOTAL', { align: 'center' });
    drawCell(xTotal, y, colTotal, r(6), fmt(mission.montant_total), { align: 'right' });
    y += r(12);

    // ── Montant en lettres, lieu et signatures ──────────────────────────
    // Le montant en lettres porte déjà « FRANCS CFA » : pas de suffixe ajouté ici.
    y = piedMission(doc, mission, y, k, {
        lettres: (ligneY) => sommeEnLettres(doc, mission.montant_total_en_lettres, ligneY, r(8.5), fullW),
        ecartNom: 26,
    });

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(r(9));
    doc.text('VISA DU CONTROLEUR FINANCIER', pageW / 2, y + r(48), { align: 'center' });

    return y + r(50);
});

window.exportMissionRegionPDF = (button, dataElId) => exporterMission(button, dataElId, 'region', (doc, k, mission) => {
    const r = (valeur) => valeur * k;
    const pageW = doc.internal.pageSize.getWidth();
    const margin = MARGE_MISSION;
    const fullW = pageW - margin * 2;
    const drawCell = (x, y, w, h, value = '', options = {}) => cellule(doc, x, y, w, h, value, options);

    // ── Titre et référence ──────────────────────────────────────────────
    let y = DEBUT_CORPS_MISSION;
    titreReference(doc, "BUDGET RELATIF A L'ORDRE DE MISSION N°", mission.reference, y, r(12), r(7));

    // ── Objet, durée, dates ─────────────────────────────────────────────
    y = objetMission(doc, 'OBJET DE LA MISSION :', mission.objet, y + r(8), k) + r(6);
    texteSouligne(doc, 'DUREE :', margin, y, r(9));
    doc.text(`${mission.nombre_jours} JOURS`, margin + r(18), y);
    doc.text(String(mission.destination || ''), pageW - margin, y, { align: 'right' });
    doc.setFont('helvetica', 'normal');
    doc.text(`DATE : Du ${mission.date_depart} au ${mission.date_retour}`, margin, y + r(5));

    // ── Tableau (10 colonnes du modèle officiel, réparties sur 190 mm) ──
    y += r(11);
    const widths = [7, 44, 10, 17, 9, 19, 17, 10, 21, 36];
    const xs = abscisses(widths);
    const largeurTable = widths.reduce((somme, largeur) => somme + largeur, 0);
    const derniere = widths.length - 1;

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(r(6));
    [
        'N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
        'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'TOTAL',
    ].forEach((entete, index) => drawCell(xs[index], y, widths[index], r(9), entete.split('\n'), { align: 'center' }));
    y += r(9);

    doc.setFontSize(r(7.5));
    drawCell(margin, y, largeurTable, r(5), 'I-  FRAIS ET INDEMNITES', { align: 'center' });
    y += r(5);
    drawCell(xs[0], y, widths[0], r(5), '');
    drawCell(xs[1], y, widths[1], r(5), 'PRENOM ET NOM', { align: 'center' });
    drawCell(xs[2], y, largeurTable - widths[0] - widths[1], r(5), '');
    y += r(5);

    const groupes = Array.isArray(mission.groupes) ? mission.groupes : [];
    groupes.forEach((groupe, indexGroupe) => {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(r(7.5));
        drawCell(margin, y, largeurTable, r(5), `${indexGroupe + 1}- ${String(groupe.libelle || '').toUpperCase()}`, { align: 'center' });
        y += r(5);

        doc.setFontSize(r(6.5));
        (groupe.lignes || []).forEach((ligne, index) => {
            doc.setFont('helvetica', 'normal');
            drawCell(xs[0], y, widths[0], r(6), index + 1, { align: 'center' });
            doc.setFont('helvetica', 'italic');
            drawCell(xs[1], y, widths[1], r(6), ligne.nom);
            doc.setFont('helvetica', 'normal');
            drawCell(xs[2], y, widths[2], r(6), '1', { align: 'center' });
            drawCell(xs[3], y, widths[3], r(6), fmt(ligne.montant_par_jour), { align: 'right' });
            drawCell(xs[4], y, widths[4], r(6), ligne.jours, { align: 'center' });
            drawCell(xs[5], y, widths[5], r(6), fmt(ligne.frais_mission), { align: 'right' });
            drawCell(xs[6], y, widths[6], r(6), fmt(ligne.montant_par_nuitee), { align: 'right' });
            drawCell(xs[7], y, widths[7], r(6), ligne.nuitees, { align: 'center' });
            drawCell(xs[8], y, widths[8], r(6), fmt(ligne.indemnites), { align: 'right' });
            drawCell(xs[9], y, widths[9], r(6), fmt(ligne.total), { align: 'right' });
            y += r(6);
        });

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(r(7.5));
        drawCell(margin, y, largeurTable - widths[derniere], r(5), `SOUS TOTAL ${indexGroupe + 1}`, { align: 'center' });
        drawCell(xs[derniere], y, widths[derniere], r(5), fmt(groupe.sous_total), { align: 'right' });
        y += r(5);
    });

    // ── II- Carburant ───────────────────────────────────────────────────
    const colLibelle = widths[0] + widths[1];
    const colVehicules = widths[2] + widths[3];
    const colQuantite = widths[4] + widths[5] + widths[6];
    const colPrix = widths[7] + widths[8];
    const colMontant = widths[derniere];
    const xVehicules = margin + colLibelle;
    const xQuantite = xVehicules + colVehicules;
    const xPrix = xQuantite + colQuantite;
    const xMontant = xPrix + colPrix;

    doc.setFont('helvetica', 'bold');
    drawCell(margin, y, largeurTable, r(5), 'II-  CARBURANT', { align: 'center' });
    y += r(5);
    // En portrait, les intitulés longs passent sur deux lignes.
    doc.setFontSize(r(6.5));
    drawCell(margin, y, colLibelle, r(8), '');
    drawCell(xVehicules, y, colVehicules, r(8), ['NBRE DE', 'VEHICULES'], { align: 'center' });
    drawCell(xQuantite, y, colQuantite, r(8), ['QTE DE CARBURANT /', 'NOMBRE JOURS'], { align: 'center' });
    drawCell(xPrix, y, colPrix, r(8), ['PRIX DU LITRE /', 'PRIX UNITAIRE'], { align: 'center' });
    drawCell(xMontant, y, colMontant, r(8), 'MONTANT', { align: 'center' });
    y += r(8);
    doc.setFontSize(r(7.5));

    const ligneTransport = (libelle, vehicules, quantite, prix, montant) => {
        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, colLibelle, r(5), libelle);
        doc.setFont('helvetica', 'normal');
        drawCell(xVehicules, y, colVehicules, r(5), vehicules, { align: 'center' });
        drawCell(xQuantite, y, colQuantite, r(5), quantite, { align: 'center' });
        drawCell(xPrix, y, colPrix, r(5), fmt(prix), { align: 'right' });
        drawCell(xMontant, y, colMontant, r(5), fmt(montant), { align: 'right' });
        y += r(5);
    };

    ligneTransport('MONTANT CARBURANT TRAJET', mission.nombre_vehicules, `${fmt(mission.litres_trajet)} L`, mission.prix_litre, mission.montant_carburant_trajet);
    ligneTransport('MONTANT CARBURANT VILLE', mission.nombre_vehicules, `${fmt(mission.litres_ville)} L`, mission.prix_litre, mission.montant_carburant_ville);
    ligneTransport('FRAIS LOCATION VEHICULE', mission.nombre_vehicules, `${mission.location_jours} jour(s)`, mission.location_tarif, mission.montant_location);
    ligneTransport("BILLET D'AVION", '', `${mission.billets_nombre} personne(s)`, mission.billets_unitaire, mission.montant_billets);

    const sousTotal2 = Number(mission.montant_carburant_trajet || 0) + Number(mission.montant_carburant_ville || 0)
        + Number(mission.montant_location || 0) + Number(mission.montant_billets || 0);

    doc.setFont('helvetica', 'bold');
    drawCell(margin, y, largeurTable - colMontant, r(5), 'SOUS-TOTAL 2', { align: 'center' });
    drawCell(xMontant, y, colMontant, r(5), fmt(sousTotal2), { align: 'right' });
    y += r(5);

    // ── III- Péages ─────────────────────────────────────────────────────
    drawCell(margin, y, largeurTable, r(5), 'III-  PEAGES', { align: 'center' });
    y += r(5);
    drawCell(margin, y, largeurTable - colMontant, r(5), 'PEAGES');
    drawCell(xMontant, y, colMontant, r(5), fmt(mission.montant_peages), { align: 'right' });
    y += r(5);

    doc.setFontSize(r(9));
    drawCell(margin, y, largeurTable - colMontant, r(6), 'TOTAL GENERAL', { align: 'center' });
    drawCell(xMontant, y, colMontant, r(6), fmt(mission.montant_total), { align: 'right' });
    y += r(12);

    // ── Montant en lettres, lieu et signatures ──────────────────────────
    y = piedMission(doc, mission, y, k, {
        lettres: (ligneY) => sommeEnLettres(doc, mission.montant_total_en_lettres, ligneY, r(8.5), fullW),
        ecartNom: 26,
    });

    return y + r(40);
});
