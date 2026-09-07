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
    doc.text(texte, textX, y + h / 2 + taille * 0.18, { align, baseline: 'middle' });
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
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
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

window.exportMissionMemeVillePDF = async function exportMissionMemeVillePDF(button, dataElId) {
    const dataEl = document.getElementById(dataElId);
    if (!dataEl) {
        window.print();
        return;
    }

    const mission = JSON.parse(dataEl.textContent);
    const originalLabel = button ? button.innerHTML : null;

    if (button) {
        button.disabled = true;
        button.dataset.loading = '1';
        button.innerHTML = 'Génération…';
    }

    const fmt = (value) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value || 0));
    const text = (doc, value, x, y, options = {}) => doc.text(String(value ?? ''), x, y, options);

    // Logo officiel : présent en clair dans la page (même origine), donc utilisable tel quel.
    const chargerLogo = async () => {
        const img = document.getElementById('mission-logo');
        if (!img) return null;
        try {
            if (!img.complete) await img.decode();
            return img.naturalWidth ? img : null;
        } catch (e) {
            return null;
        }
    };

    try {
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 10;
        const fullW = pageW - margin * 2;

        const drawCell = (x, y, w, h, value = '', options = {}) => cellule(doc, x, y, w, h, value, options);

        // Texte centré, souligné à la largeur réelle du texte.
        const souligne = (value, x, y, size) => {
            const largeur = doc.getStringUnitWidth(value) * size / doc.internal.scaleFactor;
            doc.line(x - largeur / 2, y + 1, x + largeur / 2, y + 1);
        };

        // ── En-tête officiel ────────────────────────────────────────────────
        const colonne = fullW / 2;
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

        const logo = await chargerLogo();
        if (logo) {
            doc.addImage(logo, 'PNG', centreGauche - 8, 27, 16, 16);
        }

        // ── Titre ───────────────────────────────────────────────────────────
        let y = 50;
        const titre = "BUDGET RELATIF A L'ORDRE DE MISSION N°";
        const reference = String(mission.reference || '');
        let tailleTitre = 12;
        const mesureTitre = () => (doc.getStringUnitWidth(titre + reference) * tailleTitre) / doc.internal.scaleFactor;
        while (tailleTitre > 7 && mesureTitre() > fullW) {
            tailleTitre -= 0.25;
        }
        doc.setFontSize(tailleTitre);
        const largeurTitre = doc.getStringUnitWidth(titre) * tailleTitre / doc.internal.scaleFactor;
        const largeurRef = doc.getStringUnitWidth(reference) * tailleTitre / doc.internal.scaleFactor;
        const departTitre = (pageW - (largeurTitre + largeurRef)) / 2;

        doc.text(titre, departTitre, y);
        doc.setTextColor(200, 0, 0);
        doc.text(reference, departTitre + largeurTitre, y);
        doc.setTextColor(0, 0, 0);
        doc.line(departTitre, y + 1, departTitre + largeurTitre + largeurRef, y + 1);

        // ── Objet et durée ──────────────────────────────────────────────────
        y += 8;
        doc.setFontSize(9);
        const objet = doc.splitTextToSize(`OBJET DE LA MISSION: ${String(mission.objet || '').toUpperCase()}`, fullW);
        doc.text(objet, margin, y);
        objet.forEach((ligne, index) => {
            const largeur = doc.getStringUnitWidth(ligne) * 9 / doc.internal.scaleFactor;
            doc.line(margin, y + index * 4.2 + 1, margin + largeur, y + index * 4.2 + 1);
        });

        y += objet.length * 4.2 + 6;
        doc.text('DUREE :', margin, y);
        doc.line(margin, y + 1, margin + doc.getStringUnitWidth('DUREE :') * 9 / doc.internal.scaleFactor, y + 1);
        doc.text(`${mission.nombre_jours} jour(s) ouvrable(s)`, margin + 18, y);
        doc.setFont('helvetica', 'normal');
        doc.text(`${mission.date_depart} au ${mission.date_retour}`, margin, y + 5);

        // ── Tableau (mêmes colonnes que le modèle officiel) ──────────────────
        y += 11;
        const colWidths = [9, 46, 13, 15, 13, 16, 17, 15, 19, 27];
        const colX = [];
        let cursor = margin;
        colWidths.forEach((width) => {
            colX.push(cursor);
            cursor += width;
        });
        const largeurTable = cursor - margin;
        const derniere = colWidths.length - 1;

        doc.setFontSize(6.5);
        doc.setFont('helvetica', 'bold');
        const entetes = ['N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
            'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'TOTAL'];
        entetes.forEach((entete, index) => drawCell(colX[index], y, colWidths[index], 9, entete.split('\n'), { align: 'center', valignMiddle: true }));
        y += 9;

        drawCell(margin, y, largeurTable, 5, 'I-  FRAIS ET INDEMNITES', { align: 'center', valignMiddle: true });
        y += 5;
        drawCell(colX[0], y, colWidths[0], 5, '');
        drawCell(colX[1], y, colWidths[1], 5, 'PRENOMS ET NOMS', { align: 'center', valignMiddle: true });
        drawCell(colX[2], y, largeurTable - colWidths[0] - colWidths[1], 5, '');
        y += 5;

        doc.setFont('helvetica', 'italic');
        doc.setFontSize(7);
        mission.participants.forEach((participant, index) => {
            colWidths.forEach((largeur, colonneIndex) => drawCell(colX[colonneIndex], y, largeur, 5, ''));
            doc.setFont('helvetica', 'normal');
            drawCell(colX[0], y, colWidths[0], 5, index + 1, { align: 'center', valignMiddle: true });
            doc.setFont('helvetica', 'italic');
            drawCell(colX[1], y, colWidths[1], 5, participant.nom, { valignMiddle: true });
            doc.setFont('helvetica', 'bold');
            drawCell(colX[2], y, colWidths[2], 5, '1', { align: 'center', valignMiddle: true });
            drawCell(colX[4], y, colWidths[4], 5, mission.nombre_jours, { align: 'center', valignMiddle: true });
            doc.setFont('helvetica', 'italic');
            y += 5;
        });

        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable - colWidths[derniere], 5, 'SOUS-TOTAL 1', { align: 'center', valignMiddle: true });
        drawCell(colX[derniere], y, colWidths[derniere], 5, '-', { align: 'right', valignMiddle: true });
        y += 5;

        drawCell(margin, y, largeurTable, 5, 'II-  CARBURANT', { align: 'center', valignMiddle: true });
        y += 5;
        drawCell(margin, y, largeurTable / 2, 5, 'NBRE DE JOURS OUVRABLE', { align: 'center', valignMiddle: true });
        drawCell(margin + largeurTable / 2, y, largeurTable / 2, 5, 'NOMBRE DE TICKET PAR JOUR', { align: 'center', valignMiddle: true });
        y += 5;

        const largeurRestante = largeurTable - colWidths[derniere];
        drawCell(margin, y, largeurRestante / 3, 5, 'TICKETS DE CARBURANT', { valignMiddle: true });
        doc.setFont('helvetica', 'normal');
        drawCell(margin + largeurRestante / 3, y, largeurRestante / 3, 5, mission.nombre_jours, { align: 'center', valignMiddle: true });
        drawCell(margin + (largeurRestante * 2) / 3, y, largeurRestante / 3, 5, mission.tickets_par_jour, { align: 'center', valignMiddle: true });
        doc.setFont('helvetica', 'bold');
        drawCell(colX[derniere], y, colWidths[derniere], 5, mission.nombre_tickets, { align: 'center', valignMiddle: true });
        y += 5;

        drawCell(margin, y, largeurRestante, 5, 'SOUS-TOTAL 2', { align: 'center', valignMiddle: true });
        drawCell(colX[derniere], y, colWidths[derniere], 5, mission.nombre_tickets, { align: 'center', valignMiddle: true });
        y += 5;
        doc.setFontSize(9);
        drawCell(margin, y, largeurRestante, 6, 'TOTAL', { align: 'center', valignMiddle: true });
        drawCell(colX[derniere], y, colWidths[derniere], 6, mission.nombre_tickets, { align: 'center', valignMiddle: true });
        y += 12;

        // ── Total en lettres, lieu et signatures ────────────────────────────
        doc.setFontSize(9);
        texteCentreAjuste(doc, mission.tickets_en_lettres || '', pageW / 2, y, fullW, 9);
        y += 10;

        doc.setFont('helvetica', 'normal');
        text(doc, mission.lieu_signature, pageW - margin, y, { align: 'right' });
        y += 12;

        const signataires = Array.isArray(mission.signataires) ? mission.signataires : [];
        const blockWidth = fullW / Math.max(1, signataires.length || 1);
        signataires.forEach((signataire, index) => {
            const centerX = margin + blockWidth * index + blockWidth / 2;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text(doc.splitTextToSize(String(signataire.libelle || ''), blockWidth - 6), centerX, y, { align: 'center' });

            const nom = String(signataire.nom || '');
            const nomAjuste = texteCentreAjuste(doc, nom, centerX, y + 28, blockWidth - 6, 9);
            souligne(nomAjuste.texte, centerX, y + 28, nomAjuste.taille);

            doc.setFont('helvetica', 'italic');
            doc.setFontSize(7.5);
            doc.text(doc.splitTextToSize(String(signataire.fonction || ''), blockWidth - 6), centerX, y + 33, { align: 'center' });
        });

        doc.save(`mission-${String(mission.reference || 'meme-ville').replace(/[^\w-]+/g, '_')}.pdf`);
    } catch (e) {
        console.error('Erreur export PDF mission', e);
        alert("Une erreur est survenue lors de la génération du PDF.");
    } finally {
        if (button && originalLabel !== null) {
            button.disabled = false;
            delete button.dataset.loading;
            button.innerHTML = originalLabel;
        }
    }
};

window.exportMissionExterieurePDF = async function exportMissionExterieurePDF(button, dataElId) {
    const dataEl = document.getElementById(dataElId);
    if (!dataEl) {
        window.print();
        return;
    }

    const mission = JSON.parse(dataEl.textContent);
    const originalLabel = button ? button.innerHTML : null;

    if (button) {
        button.disabled = true;
        button.dataset.loading = '1';
        button.innerHTML = 'Génération…';
    }

    const fmt = (value) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value || 0));

    // Logo officiel : présent en clair dans la page (même origine), donc utilisable tel quel.
    const chargerLogo = async () => {
        const img = document.getElementById('mission-logo');
        if (!img) return null;
        try {
            if (!img.complete) await img.decode();
            return img.naturalWidth ? img : null;
        } catch (e) {
            return null;
        }
    };

    try {
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 10;
        const fullW = pageW - margin * 2;

        const drawCell = (x, y, w, h, value = '', options = {}) => cellule(doc, x, y, w, h, value, options);

        const souligne = (value, x, y, size) => {
            const largeur = doc.getStringUnitWidth(value) * size / doc.internal.scaleFactor;
            doc.line(x - largeur / 2, y + 1, x + largeur / 2, y + 1);
        };

        // ── En-tête officiel ────────────────────────────────────────────────
        const colonne = fullW / 2;
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

        const logo = await chargerLogo();
        if (logo) {
            doc.addImage(logo, 'PNG', centreGauche - 8, 27, 16, 16);
        }

        // ── Titre, référence en rouge ───────────────────────────────────────
        let y = 50;
        const titre = "PROJET DE BUDGET RELATIF A LA LEVEE D'ORDRE DE MISSION N°";
        const reference = String(mission.reference || '');
        let tailleTitre = 11;
        const mesureTitre = () => (doc.getStringUnitWidth(titre + reference) * tailleTitre) / doc.internal.scaleFactor;
        while (tailleTitre > 7 && mesureTitre() > fullW) {
            tailleTitre -= 0.25;
        }
        doc.setFontSize(tailleTitre);
        const largeurTitre = doc.getStringUnitWidth(titre) * tailleTitre / doc.internal.scaleFactor;
        const largeurRef = doc.getStringUnitWidth(reference) * tailleTitre / doc.internal.scaleFactor;
        const departTitre = (pageW - (largeurTitre + largeurRef)) / 2;

        doc.text(titre, departTitre, y);
        doc.setTextColor(200, 0, 0);
        doc.text(reference, departTitre + largeurTitre, y);
        doc.setTextColor(0, 0, 0);
        doc.line(departTitre, y + 1, departTitre + largeurTitre + largeurRef, y + 1);

        // ── Objet et durée ──────────────────────────────────────────────────
        y += 8;
        doc.setFontSize(9);
        const objet = doc.splitTextToSize(`OBJET DE LA MISSION : ${String(mission.objet || '').toUpperCase()}`, fullW);
        doc.text(objet, pageW / 2, y, { align: 'center' });
        objet.forEach((ligne, index) => souligne(ligne, pageW / 2, y + index * 4.2, 9));

        y += objet.length * 4.2 + 6;
        doc.text('DUREE MISSION :', margin, y);
        doc.line(margin, y + 1, margin + doc.getStringUnitWidth('DUREE MISSION :') * 9 / doc.internal.scaleFactor, y + 1);
        doc.text(`${mission.nombre_jours} jour(s)`, margin + 32, y);
        doc.text(String(mission.destination || ''), pageW - margin, y, { align: 'right' });

        doc.setFont('helvetica', 'normal');
        doc.text(`DUREE : Du ${mission.date_depart} au ${mission.date_retour}`, margin, y + 5);
        doc.setFontSize(8);
        doc.text(String(mission.zone_label || ''), pageW - margin, y + 5, { align: 'right' });

        // ── Tableau (12 colonnes du modèle officiel) ────────────────────────
        y += 11;
        const widths = [10, 55, 14, 22, 12, 24, 22, 14, 24, 26, 28, 26];
        const xs = [];
        let cursor = margin;
        widths.forEach((w) => {
            xs.push(cursor);
            cursor += w;
        });
        const largeurTable = cursor - margin;
        const derniere = widths.length - 1;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(6.5);
        [
            'N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
            'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'SOUS TOTAL',
            `Taux de majoration\npar zone ${Number(mission.zone_taux || 0)}%`, 'TOTAL\nGENERAL',
        ].forEach((entete, index) => drawCell(xs[index], y, widths[index], 11, entete.split('\n'), { align: 'center', middle: true }));
        y += 11;

        doc.setFontSize(7.5);
        drawCell(margin, y, largeurTable, 5, 'I-  FRAIS ET INDEMNITES', { align: 'center', middle: true });
        y += 5;
        drawCell(xs[0], y, widths[0], 5, '');
        drawCell(xs[1], y, widths[1], 5, 'PRENOMS ET NOMS', { align: 'center', middle: true });
        drawCell(xs[2], y, largeurTable - widths[0] - widths[1], 5, '');
        y += 5;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(7);
        mission.participants.forEach((participant, index) => {
            const hauteur = 6;
            drawCell(xs[0], y, widths[0], hauteur, index + 1, { align: 'center', middle: true });
            doc.setFont('helvetica', 'italic');
            drawCell(xs[1], y, widths[1], hauteur, participant.nom, { middle: true });
            doc.setFont('helvetica', 'normal');
            drawCell(xs[2], y, widths[2], hauteur, '1', { align: 'center', middle: true });
            drawCell(xs[3], y, widths[3], hauteur, fmt(participant.montant_par_jour), { align: 'right', middle: true });
            drawCell(xs[4], y, widths[4], hauteur, mission.nombre_jours, { align: 'center', middle: true });
            drawCell(xs[5], y, widths[5], hauteur, fmt(Number(participant.montant_par_jour || 0) * Number(mission.nombre_jours || 0)), { align: 'right', middle: true });
            drawCell(xs[6], y, widths[6], hauteur, fmt(participant.montant_par_nuitee), { align: 'right', middle: true });
            drawCell(xs[7], y, widths[7], hauteur, participant.nombre_nuitees, { align: 'center', middle: true });
            drawCell(xs[8], y, widths[8], hauteur, fmt(Number(participant.montant_par_nuitee || 0) * Number(participant.nombre_nuitees || 0)), { align: 'right', middle: true });
            drawCell(xs[9], y, widths[9], hauteur, fmt(participant.sous_total), { align: 'right', middle: true });
            drawCell(xs[10], y, widths[10], hauteur, fmt(participant.majoration_montant), { align: 'right', middle: true });
            drawCell(xs[11], y, widths[11], hauteur, fmt(participant.total_general), { align: 'right', middle: true });
            y += hauteur;
        });

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7.5);
        drawCell(margin, y, largeurTable - widths[derniere], 5, 'SOUS TOTAL 1', { align: 'center', middle: true });
        drawCell(xs[derniere], y, widths[derniere], 5, fmt(Number(mission.montant_indemnites || 0) + Number(mission.montant_majoration || 0)), { align: 'right', middle: true });
        y += 5;

        // Sections II et III : mêmes colonnes fusionnées que le modèle.
        const colLibelle = widths[0] + widths[1];
        const colNombre = widths[2] + widths[3] + widths[4] + widths[5];
        const colUnitaire = widths[6] + widths[7] + widths[8];
        const colTotal = largeurTable - colLibelle - colNombre - colUnitaire;
        const xNombre = margin + colLibelle;
        const xUnitaire = xNombre + colNombre;
        const xTotal = xUnitaire + colUnitaire;

        const ligneDetail = (libelle, nombre, unitaire, total) => {
            doc.setFont('helvetica', 'bold');
            drawCell(margin, y, colLibelle, 5, libelle, { middle: true });
            doc.setFont('helvetica', 'normal');
            drawCell(xNombre, y, colNombre, 5, nombre, { align: 'center', middle: true });
            drawCell(xUnitaire, y, colUnitaire, 5, fmt(unitaire), { align: 'right', middle: true });
            drawCell(xTotal, y, colTotal, 5, fmt(total), { align: 'right', middle: true });
            y += 5;
        };

        const sousTotal = (libelle, valeur) => {
            doc.setFont('helvetica', 'bold');
            drawCell(margin, y, largeurTable - colTotal, 5, libelle, { align: 'center', middle: true });
            drawCell(xTotal, y, colTotal, 5, fmt(valeur), { align: 'right', middle: true });
            y += 5;
        };

        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable, 5, 'II-  AUTRES FRAIS', { align: 'center', middle: true });
        y += 5;
        drawCell(margin, y, colLibelle, 5, '');
        drawCell(xNombre, y, colNombre, 5, 'NBRE DE PERSONNES', { align: 'center', middle: true });
        drawCell(xUnitaire, y, colUnitaire, 5, 'MONTANT PAR PERSONNE', { align: 'center', middle: true });
        drawCell(xTotal, y, colTotal, 5, 'MONTANT TOTAL', { align: 'center', middle: true });
        y += 5;
        ligneDetail('FRAIS DE PARTICIPATION', mission.frais_participation_nombre, mission.frais_participation_unitaire, mission.frais_participation_total);
        ligneDetail('FRAIS DE VISA', mission.frais_visa_nombre, mission.frais_visa_unitaire, mission.frais_visa_total);
        sousTotal('SOUS TOTAL 2', mission.montant_autres_frais);

        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable, 5, "III-  BILLETS D'AVION", { align: 'center', middle: true });
        y += 5;
        drawCell(margin, y, colLibelle, 5, '');
        drawCell(xNombre, y, colNombre, 5, 'NBRE DE PERSONNES', { align: 'center', middle: true });
        drawCell(xUnitaire, y, colUnitaire, 5, "PRIX D'UN BILLET", { align: 'center', middle: true });
        drawCell(xTotal, y, colTotal, 5, 'MONTANT TOTAL', { align: 'center', middle: true });
        y += 5;
        ligneDetail('CLASSE AFFAIRE', mission.billets_affaire_nombre, mission.billets_affaire_unitaire, mission.billets_affaire_total);
        ligneDetail('CLASSE ECONOMIQUE', mission.billets_economique_nombre, mission.billets_economique_unitaire, mission.billets_economique_total);
        sousTotal('SOUS TOTAL 3', mission.montant_billets);

        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable - colTotal, 6, 'TOTAL', { align: 'center', middle: true });
        drawCell(xTotal, y, colTotal, 6, fmt(mission.montant_total), { align: 'right', middle: true });
        y += 12;

        // ── Montant en lettres, lieu et signatures ──────────────────────────
        doc.setFontSize(8.5);
        // Le montant en lettres porte déjà « FRANCS CFA » : pas de suffixe ajouté ici.
        const prefixe = 'ARRETE A LA SOMME DE : ';
        const lettres = String(mission.montant_total_en_lettres || '');
        let tailleSomme = 8.5;
        const mesureSomme = () => (doc.getStringUnitWidth(prefixe + lettres) * tailleSomme) / doc.internal.scaleFactor;
        while (tailleSomme > 5 && mesureSomme() > fullW) {
            tailleSomme -= 0.25;
        }
        doc.setFontSize(tailleSomme);
        const largeurPrefixe = doc.getStringUnitWidth(prefixe) * tailleSomme / doc.internal.scaleFactor;
        const largeurLettres = doc.getStringUnitWidth(lettres) * tailleSomme / doc.internal.scaleFactor;
        const departSomme = (pageW - (largeurPrefixe + largeurLettres)) / 2;

        doc.text(prefixe, departSomme, y);
        doc.setTextColor(200, 0, 0);
        doc.text(lettres, departSomme + largeurPrefixe, y);
        doc.setTextColor(0, 0, 0);
        y += 10;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.text(mission.lieu_signature, pageW - margin, y, { align: 'right' });
        y += 12;

        const signataires = Array.isArray(mission.signataires) ? mission.signataires : [];
        const blockWidth = fullW / Math.max(1, signataires.length || 1);
        signataires.forEach((signataire, index) => {
            const centerX = margin + (blockWidth * index) + blockWidth / 2;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text(doc.splitTextToSize(String(signataire.libelle || ''), blockWidth - 6), centerX, y, { align: 'center' });

            const nom = String(signataire.nom || '');
            const nomAjuste = texteCentreAjuste(doc, nom, centerX, y + 26, blockWidth - 6, 9);
            souligne(nomAjuste.texte, centerX, y + 26, nomAjuste.taille);

            doc.setFont('helvetica', 'italic');
            doc.setFontSize(7.5);
            doc.text(doc.splitTextToSize(String(signataire.fonction || ''), blockWidth - 6), centerX, y + 31, { align: 'center' });
        });

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(9);
        doc.text('VISA DU CONTROLEUR FINANCIER', pageW / 2, y + 48, { align: 'center' });

        doc.save(`mission-${String(mission.reference || 'exterieure').replace(/[^\w-]+/g, '_')}.pdf`);
    } catch (e) {
        console.error('Erreur export PDF mission extérieure', e);
        alert("Une erreur est survenue lors de la génération du PDF.");
    } finally {
        if (button && originalLabel !== null) {
            button.disabled = false;
            delete button.dataset.loading;
            button.innerHTML = originalLabel;
        }
    }
};

window.exportMissionRegionPDF = async function exportMissionRegionPDF(button, dataElId) {
    const dataEl = document.getElementById(dataElId);
    if (!dataEl) {
        window.print();
        return;
    }

    const mission = JSON.parse(dataEl.textContent);
    const originalLabel = button ? button.innerHTML : null;

    if (button) {
        button.disabled = true;
        button.dataset.loading = '1';
        button.innerHTML = 'Génération…';
    }

    const fmt = (value) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value || 0));

    const chargerLogo = async () => {
        const img = document.getElementById('mission-logo');
        if (!img) return null;
        try {
            if (!img.complete) await img.decode();
            return img.naturalWidth ? img : null;
        } catch (e) {
            return null;
        }
    };

    try {
        // Paysage : les dix colonnes chiffrées du modèle ne tiennent pas en portrait.
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 10;
        const fullW = pageW - margin * 2;

        const drawCell = (x, y, w, h, value = '', options = {}) => cellule(doc, x, y, w, h, value, options);

        const souligne = (value, x, y, size) => {
            const largeur = doc.getStringUnitWidth(value) * size / doc.internal.scaleFactor;
            doc.line(x - largeur / 2, y + 1, x + largeur / 2, y + 1);
        };

        // ── En-tête officiel ────────────────────────────────────────────────
        const colonne = fullW / 2;
        const centreGauche = margin + colonne / 2;
        const centreDroite = margin + colonne + colonne / 2;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(8);
        doc.text('MINISTERE DE LA SANTE ET DU DEVELOPPEMENT SOCIAL', centreGauche, 14, { align: 'center' });
        doc.text('------------------------', centreGauche, 18, { align: 'center' });
        doc.text("CAISSE NATIONALE D'ASSURANCE MALADIE", centreGauche, 22, { align: 'center' });

        doc.text('REPUBLIQUE DU MALI', centreDroite, 14, { align: 'center' });
        doc.text('----------------------', centreDroite, 18, { align: 'center' });
        doc.text('UN PEUPLE – UN BUT – UNE FOI', centreDroite, 22, { align: 'center' });

        const logo = await chargerLogo();
        if (logo) {
            doc.addImage(logo, 'PNG', centreGauche - 8, 25, 16, 16);
        }

        // ── Titre, référence en rouge ───────────────────────────────────────
        let y = 48;
        const titre = "BUDGET RELATIF A L'ORDRE DE MISSION N°";
        const reference = String(mission.reference || '');
        let tailleTitre = 12;
        const mesureTitre = () => (doc.getStringUnitWidth(titre + reference) * tailleTitre) / doc.internal.scaleFactor;
        while (tailleTitre > 7 && mesureTitre() > fullW) {
            tailleTitre -= 0.25;
        }
        doc.setFontSize(tailleTitre);
        const largeurTitre = doc.getStringUnitWidth(titre) * tailleTitre / doc.internal.scaleFactor;
        const largeurRef = doc.getStringUnitWidth(reference) * tailleTitre / doc.internal.scaleFactor;
        const departTitre = (pageW - (largeurTitre + largeurRef)) / 2;

        doc.text(titre, departTitre, y);
        doc.setTextColor(200, 0, 0);
        doc.text(reference, departTitre + largeurTitre, y);
        doc.setTextColor(0, 0, 0);
        doc.line(departTitre, y + 1, departTitre + largeurTitre + largeurRef, y + 1);

        // ── Objet, durée, dates ─────────────────────────────────────────────
        y += 8;
        doc.setFontSize(9);
        const objet = doc.splitTextToSize(`OBJET DE LA MISSION : ${String(mission.objet || '').toUpperCase()}`, fullW);
        doc.text(objet, margin, y);
        objet.forEach((ligne, index) => {
            const largeur = doc.getStringUnitWidth(ligne) * 9 / doc.internal.scaleFactor;
            doc.line(margin, y + index * 4.2 + 1, margin + largeur, y + index * 4.2 + 1);
        });

        y += objet.length * 4.2 + 6;
        doc.text('DUREE :', margin, y);
        doc.line(margin, y + 1, margin + doc.getStringUnitWidth('DUREE :') * 9 / doc.internal.scaleFactor, y + 1);
        doc.setTextColor(200, 0, 0);
        doc.text(`${mission.nombre_jours} JOURS`, margin + 18, y);
        doc.setTextColor(0, 0, 0);
        doc.text(String(mission.destination || ''), pageW - margin, y, { align: 'right' });
        doc.setFont('helvetica', 'normal');
        doc.text(`DATE : Du ${mission.date_depart} au ${mission.date_retour}`, margin, y + 5);

        // ── Tableau (10 colonnes du modèle officiel) ────────────────────────
        y += 11;
        const widths = [10, 62, 16, 26, 14, 28, 26, 16, 30, 49];
        const xs = [];
        let cursor = margin;
        widths.forEach((w) => {
            xs.push(cursor);
            cursor += w;
        });
        const largeurTable = cursor - margin;
        const derniere = widths.length - 1;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(6.5);
        [
            'N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
            'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'TOTAL',
        ].forEach((entete, index) => drawCell(xs[index], y, widths[index], 10, entete.split('\n'), { align: 'center', middle: true }));
        y += 10;

        doc.setFontSize(7.5);
        drawCell(margin, y, largeurTable, 5, 'I-  FRAIS ET INDEMNITES', { align: 'center', middle: true });
        y += 5;
        drawCell(xs[0], y, widths[0], 5, '');
        drawCell(xs[1], y, widths[1], 5, 'PRENOM ET NOM', { align: 'center', middle: true });
        drawCell(xs[2], y, largeurTable - widths[0] - widths[1], 5, '');
        y += 5;

        const groupes = Array.isArray(mission.groupes) ? mission.groupes : [];
        groupes.forEach((groupe, indexGroupe) => {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(7.5);
            drawCell(margin, y, largeurTable, 5, `${indexGroupe + 1}- ${String(groupe.libelle || '').toUpperCase()}`, { align: 'center', middle: true });
            y += 5;

            doc.setFontSize(7);
            (groupe.lignes || []).forEach((ligne, index) => {
                doc.setFont('helvetica', 'normal');
                drawCell(xs[0], y, widths[0], 6, index + 1, { align: 'center', middle: true });
                doc.setFont('helvetica', 'italic');
                drawCell(xs[1], y, widths[1], 6, ligne.nom, { middle: true });
                doc.setFont('helvetica', 'normal');
                drawCell(xs[2], y, widths[2], 6, '1', { align: 'center', middle: true });
                drawCell(xs[3], y, widths[3], 6, fmt(ligne.montant_par_jour), { align: 'right', middle: true });
                drawCell(xs[4], y, widths[4], 6, ligne.jours, { align: 'center', middle: true });
                drawCell(xs[5], y, widths[5], 6, fmt(ligne.frais_mission), { align: 'right', middle: true });
                drawCell(xs[6], y, widths[6], 6, fmt(ligne.montant_par_nuitee), { align: 'right', middle: true });
                drawCell(xs[7], y, widths[7], 6, ligne.nuitees, { align: 'center', middle: true });
                drawCell(xs[8], y, widths[8], 6, fmt(ligne.indemnites), { align: 'right', middle: true });
                drawCell(xs[9], y, widths[9], 6, fmt(ligne.total), { align: 'right', middle: true });
                y += 6;
            });

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(7.5);
            drawCell(margin, y, largeurTable - widths[derniere], 5, `SOUS TOTAL ${indexGroupe + 1}`, { align: 'center', middle: true });
            drawCell(xs[derniere], y, widths[derniere], 5, fmt(groupe.sous_total), { align: 'right', middle: true });
            y += 5;
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
        drawCell(margin, y, largeurTable, 5, 'II-  CARBURANT', { align: 'center', middle: true });
        y += 5;
        drawCell(margin, y, colLibelle, 5, '');
        drawCell(xVehicules, y, colVehicules, 5, 'NBRE DE VEHICULES', { align: 'center', middle: true });
        drawCell(xQuantite, y, colQuantite, 5, 'QTE DE CARBURANT / NOMBRE JOURS', { align: 'center', middle: true });
        drawCell(xPrix, y, colPrix, 5, "PRIX DU LITRE / PRIX UNITAIRE", { align: 'center', middle: true });
        drawCell(xMontant, y, colMontant, 5, 'MONTANT', { align: 'center', middle: true });
        y += 5;

        const ligneTransport = (libelle, vehicules, quantite, prix, montant) => {
            doc.setFont('helvetica', 'bold');
            drawCell(margin, y, colLibelle, 5, libelle, { middle: true });
            doc.setFont('helvetica', 'normal');
            drawCell(xVehicules, y, colVehicules, 5, vehicules, { align: 'center', middle: true });
            drawCell(xQuantite, y, colQuantite, 5, quantite, { align: 'center', middle: true });
            drawCell(xPrix, y, colPrix, 5, fmt(prix), { align: 'right', middle: true });
            drawCell(xMontant, y, colMontant, 5, fmt(montant), { align: 'right', middle: true });
            y += 5;
        };

        ligneTransport('MONTANT CARBURANT TRAJET', mission.nombre_vehicules, `${fmt(mission.litres_trajet)} L`, mission.prix_litre, mission.montant_carburant_trajet);
        ligneTransport('MONTANT CARBURANT VILLE', mission.nombre_vehicules, `${fmt(mission.litres_ville)} L`, mission.prix_litre, mission.montant_carburant_ville);
        ligneTransport('FRAIS LOCATION VEHICULE', mission.nombre_vehicules, `${mission.location_jours} jour(s)`, mission.location_tarif, mission.montant_location);
        ligneTransport("BILLET D'AVION", '', `${mission.billets_nombre} personne(s)`, mission.billets_unitaire, mission.montant_billets);

        const sousTotal2 = Number(mission.montant_carburant_trajet || 0) + Number(mission.montant_carburant_ville || 0)
            + Number(mission.montant_location || 0) + Number(mission.montant_billets || 0);

        doc.setFont('helvetica', 'bold');
        drawCell(margin, y, largeurTable - colMontant, 5, 'SOUS-TOTAL 2', { align: 'center', middle: true });
        drawCell(xMontant, y, colMontant, 5, fmt(sousTotal2), { align: 'right', middle: true });
        y += 5;

        // ── III- Péages ─────────────────────────────────────────────────────
        drawCell(margin, y, largeurTable, 5, 'III-  PEAGES', { align: 'center', middle: true });
        y += 5;
        drawCell(margin, y, largeurTable - colMontant, 5, 'PEAGES', { middle: true });
        drawCell(xMontant, y, colMontant, 5, fmt(mission.montant_peages), { align: 'right', middle: true });
        y += 5;

        doc.setFontSize(9);
        drawCell(margin, y, largeurTable - colMontant, 6, 'TOTAL GENERAL', { align: 'center', middle: true });
        drawCell(xMontant, y, colMontant, 6, fmt(mission.montant_total), { align: 'right', middle: true });
        y += 12;

        // ── Montant en lettres, lieu et signatures ──────────────────────────
        doc.setFontSize(8.5);
        const prefixe = 'ARRETE A LA SOMME DE : ';
        const lettres = String(mission.montant_total_en_lettres || '');
        let tailleSomme = 8.5;
        const mesureSomme = () => (doc.getStringUnitWidth(prefixe + lettres) * tailleSomme) / doc.internal.scaleFactor;
        while (tailleSomme > 5 && mesureSomme() > fullW) {
            tailleSomme -= 0.25;
        }
        doc.setFontSize(tailleSomme);
        const largeurPrefixe = doc.getStringUnitWidth(prefixe) * tailleSomme / doc.internal.scaleFactor;
        const largeurLettres = doc.getStringUnitWidth(lettres) * tailleSomme / doc.internal.scaleFactor;
        const departSomme = (pageW - (largeurPrefixe + largeurLettres)) / 2;

        doc.text(prefixe, departSomme, y);
        doc.setTextColor(200, 0, 0);
        doc.text(lettres, departSomme + largeurPrefixe, y);
        doc.setTextColor(0, 0, 0);
        y += 10;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.text(mission.lieu_signature, pageW - margin, y, { align: 'right' });
        y += 12;

        const signataires = Array.isArray(mission.signataires) ? mission.signataires : [];
        const blockWidth = fullW / Math.max(1, signataires.length || 1);
        signataires.forEach((signataire, index) => {
            const centerX = margin + (blockWidth * index) + blockWidth / 2;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text(doc.splitTextToSize(String(signataire.libelle || ''), blockWidth - 6), centerX, y, { align: 'center' });

            const nom = String(signataire.nom || '');
            const nomAjuste = texteCentreAjuste(doc, nom, centerX, y + 26, blockWidth - 6, 9);
            souligne(nomAjuste.texte, centerX, y + 26, nomAjuste.taille);

            doc.setFont('helvetica', 'italic');
            doc.setFontSize(7.5);
            doc.text(doc.splitTextToSize(String(signataire.fonction || ''), blockWidth - 6), centerX, y + 31, { align: 'center' });
        });

        doc.save(`mission-${String(mission.reference || 'region').replace(/[^\w-]+/g, '_')}.pdf`);
    } catch (e) {
        console.error('Erreur export PDF mission région', e);
        alert("Une erreur est survenue lors de la génération du PDF.");
    } finally {
        if (button && originalLabel !== null) {
            button.disabled = false;
            delete button.dataset.loading;
            button.innerHTML = originalLabel;
        }
    }
};
