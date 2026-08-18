import { jsPDF } from 'jspdf';

window.jspdf = window.jspdf || { jsPDF };

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

    try {
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 10;
        const fullW = pageW - margin * 2;
        const colWidths = [10, 52, 16, 18, 16, 20, 18, 18, 24, 18];
        const colX = [];
        let cursor = margin;

        colWidths.forEach((width) => {
            colX.push(cursor);
            cursor += width;
        });

        const drawCell = (x, y, w, h, value = '', options = {}) => {
            doc.rect(x, y, w, h);
            const align = options.align || 'left';
            const padX = align === 'right' ? w - 1.5 : align === 'center' ? w / 2 : 1.5;
            const textX = align === 'right' ? x + padX : align === 'center' ? x + padX : x + padX;
            const textY = y + (options.valignMiddle ? h / 2 + 1 : 4);
            const lines = Array.isArray(value) ? value : doc.splitTextToSize(String(value ?? ''), Math.max(5, w - 3));
            doc.text(lines, textX, textY, {
                align,
                baseline: options.valignMiddle ? 'middle' : 'alphabetic',
            });
        };

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        drawCell(margin, 12, fullW, 10, `BUDGET RELATIF A L'ORDRE DE MISSION N°${mission.reference}`.toUpperCase(), { align: 'center', valignMiddle: true });
        drawCell(margin, 26, fullW, 18, `OBJET DE LA MISSION: ${mission.objet}`.toUpperCase());
        drawCell(margin, 48, fullW / 2, 8, `DUREE : ${mission.nombre_jours} jour(s)`.toUpperCase(), { valignMiddle: true });
        drawCell(margin + fullW / 2, 48, fullW / 2, 8, `${mission.date_depart} au ${mission.date_retour}`, { valignMiddle: true });

        let y = 60;
        doc.setFontSize(8);
        const headers = [
            'N°',
            'LIBELLE',
            'Nbre de\npers.',
            'Mtant\npar jour',
            'Nbre\nde Jrs',
            'Frais de\nmission',
            'Mtant\npar nuitée',
            'Nbre de\nnuitées',
            'Indemnités\nde Mission',
            'TOTAL',
        ];

        headers.forEach((header, index) => drawCell(colX[index], y, colWidths[index], 10, header, { align: 'center', valignMiddle: true }));
        y += 10;
        drawCell(margin, y, fullW, 7, 'I-  FRAIS ET INDEMNITES', { valignMiddle: true });
        y += 7;
        drawCell(colX[0], y, colWidths[0], 7, '', { valignMiddle: true });
        drawCell(colX[1], y, fullW - colWidths[0], 7, 'PRENOMS ET NOMS', { valignMiddle: true });
        y += 7;

        mission.participants.forEach((participant, index) => {
            const rowTotal = Number(mission.montant_par_jour || 0) * Number(mission.nombre_jours || 0);

            drawCell(colX[0], y, colWidths[0], 7, index + 1, { align: 'center', valignMiddle: true });
            drawCell(colX[1], y, colWidths[1], 7, participant.nom);
            drawCell(colX[2], y, colWidths[2], 7, '1', { align: 'center', valignMiddle: true });
            drawCell(colX[3], y, colWidths[3], 7, fmt(mission.montant_par_jour), { align: 'right', valignMiddle: true });
            drawCell(colX[4], y, colWidths[4], 7, mission.nombre_jours, { align: 'center', valignMiddle: true });
            drawCell(colX[5], y, colWidths[5], 7, fmt(rowTotal), { align: 'right', valignMiddle: true });
            drawCell(colX[6], y, colWidths[6], 7, '-', { align: 'center', valignMiddle: true });
            drawCell(colX[7], y, colWidths[7], 7, '-', { align: 'center', valignMiddle: true });
            drawCell(colX[8], y, colWidths[8], 7, fmt(rowTotal), { align: 'right', valignMiddle: true });
            drawCell(colX[9], y, colWidths[9], 7, fmt(rowTotal), { align: 'right', valignMiddle: true });
            y += 7;
        });

        drawCell(margin, y, fullW - colWidths[9], 7, 'SOUS-TOTAL 1', { align: 'right', valignMiddle: true });
        drawCell(colX[9], y, colWidths[9], 7, fmt(mission.montant_indemnites), { align: 'right', valignMiddle: true });
        y += 7;
        drawCell(margin, y, fullW, 7, 'II-  CARBURANT', { valignMiddle: true });
        y += 7;
        drawCell(margin, y, fullW / 2, 7, `NBRE DE JOURS OUVRABLE : ${mission.nombre_jours}`, { valignMiddle: true });
        drawCell(margin + fullW / 2, y, fullW / 2, 7, `NOMBRE DE TICKET PAR JOUR : ${mission.tickets_par_jour}`, { valignMiddle: true });
        y += 7;
        drawCell(margin, y, fullW - colWidths[9], 7, 'MONTANT CARBURANT', { valignMiddle: true });
        drawCell(colX[9], y, colWidths[9], 7, fmt(mission.montant_carburant), { align: 'right', valignMiddle: true });
        y += 7;
        drawCell(margin, y, fullW - colWidths[9], 7, 'SOUS-TOTAL 2', { align: 'right', valignMiddle: true });
        drawCell(colX[9], y, colWidths[9], 7, fmt(mission.montant_carburant), { align: 'right', valignMiddle: true });
        y += 7;
        drawCell(margin, y, fullW - colWidths[9], 7, 'TOTAL', { align: 'right', valignMiddle: true });
        drawCell(colX[9], y, colWidths[9], 7, fmt(mission.montant_total), { align: 'right', valignMiddle: true });
        y += 11;

        drawCell(margin, y, fullW, 7, mission.tickets_en_lettres, { align: 'center', valignMiddle: true });
        y += 14;

        text(doc, `${mission.lieu_signature} le ${mission.date_document}`, pageW - margin, y, { align: 'right' });
        y += 12;

        const signataires = Array.isArray(mission.signataires) ? mission.signataires : [];
        const blockWidth = fullW / Math.max(1, signataires.length || 1);
        signataires.forEach((signataire, index) => {
            const startX = margin + blockWidth * index;
            const centerX = startX + blockWidth / 2;

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(9);
            text(doc, String(signataire.libelle || ''), centerX, y, { align: 'center' });
            doc.setFontSize(10);
            text(doc, String(signataire.nom || ''), centerX, y + 28, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            const fonction = doc.splitTextToSize(String(signataire.fonction || ''), blockWidth - 6);
            text(doc, fonction, centerX, y + 34, { align: 'center' });
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

    try {
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const pageW = doc.internal.pageSize.getWidth();
        const margin = 8;
        const fullW = pageW - margin * 2;

        const drawCell = (x, y, w, h, value = '', options = {}) => {
            doc.rect(x, y, w, h);
            const align = options.align || 'left';
            const textX = align === 'right' ? x + w - 1.5 : align === 'center' ? x + w / 2 : x + 1.5;
            const lines = Array.isArray(value) ? value : doc.splitTextToSize(String(value ?? ''), Math.max(5, w - 3));
            doc.text(lines, textX, y + (options.middle ? h / 2 + 1 : 4), {
                align,
                baseline: options.middle ? 'middle' : 'alphabetic',
            });
        };

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        drawCell(margin, 10, fullW, 10, `PROJET DE BUDGET RELATIF A LA LEVEE D'ORDRE DE MISSION N°${mission.reference}`.toUpperCase(), { align: 'center', middle: true });
        drawCell(margin, 24, fullW, 14, `OBJET DE LA MISSION: ${mission.objet}`.toUpperCase());
        drawCell(margin, 42, fullW / 2, 8, `DUREE MISSION: ${mission.nombre_jours} jour(s)`, { middle: true });
        drawCell(margin + fullW / 2, 42, fullW / 2, 8, `DESTINATION: ${mission.destination || ''}`, { middle: true });
        drawCell(margin, 54, fullW, 8, `DUREE : Du ${mission.date_depart} au ${mission.date_retour}`, { middle: true });

        const widths = [10, 46, 12, 18, 14, 18, 18, 16, 18, 20, 20, 20];
        const xs = [];
        let cursor = margin;
        widths.forEach((w) => {
            xs.push(cursor);
            cursor += w;
        });

        let y = 68;
        doc.setFontSize(7);
        [
            'N°', 'LIBELLE', 'Nbre de\npers.', 'Mtant\npar jour', 'Nbre\nde Jrs', 'Frais de\nmission',
            'Mtant\npar nuitée', 'Nbre de\nnuitées', 'Indemnités\nde Mission', 'SOUS TOTAL',
            `Majoration\n${Number(mission.zone_taux || 0)}%`, 'TOTAL GENERAL',
        ].forEach((header, index) => drawCell(xs[index], y, widths[index], 10, header, { align: 'center', middle: true }));
        y += 10;
        drawCell(margin, y, fullW, 7, 'I-  FRAIS ET INDEMNITES', { middle: true });
        y += 7;
        drawCell(xs[0], y, widths[0], 7, '', { middle: true });
        drawCell(xs[1], y, fullW - widths[0], 7, `PRENOMS ET NOMS — ${mission.zone_label || ''}`, { middle: true });
        y += 7;

        mission.participants.forEach((participant, index) => {
            drawCell(xs[0], y, widths[0], 8, index + 1, { align: 'center', middle: true });
            drawCell(xs[1], y, widths[1], 8, [participant.nom, participant.categorie].filter(Boolean));
            drawCell(xs[2], y, widths[2], 8, '1', { align: 'center', middle: true });
            drawCell(xs[3], y, widths[3], 8, fmt(participant.montant_par_jour), { align: 'right', middle: true });
            drawCell(xs[4], y, widths[4], 8, mission.nombre_jours, { align: 'center', middle: true });
            drawCell(xs[5], y, widths[5], 8, fmt(Number(participant.montant_par_jour || 0) * Number(mission.nombre_jours || 0)), { align: 'right', middle: true });
            drawCell(xs[6], y, widths[6], 8, fmt(participant.montant_par_nuitee), { align: 'right', middle: true });
            drawCell(xs[7], y, widths[7], 8, participant.nombre_nuitees, { align: 'center', middle: true });
            drawCell(xs[8], y, widths[8], 8, fmt(Number(participant.montant_par_nuitee || 0) * Number(participant.nombre_nuitees || 0)), { align: 'right', middle: true });
            drawCell(xs[9], y, widths[9], 8, fmt(participant.sous_total), { align: 'right', middle: true });
            drawCell(xs[10], y, widths[10], 8, fmt(participant.majoration_montant), { align: 'right', middle: true });
            drawCell(xs[11], y, widths[11], 8, fmt(participant.total_general), { align: 'right', middle: true });
            y += 8;
        });

        drawCell(margin, y, fullW - widths[11], 7, 'SOUS TOTAL 1', { align: 'right', middle: true });
        drawCell(xs[11], y, widths[11], 7, fmt(Number(mission.montant_indemnites || 0) + Number(mission.montant_majoration || 0)), { align: 'right', middle: true });
        y += 10;

        drawCell(margin, y, 100, 7, 'II-  AUTRES FRAIS', { middle: true });
        y += 7;
        drawCell(margin, y, 70, 7, 'FRAIS DE PARTICIPATION', { middle: true });
        drawCell(margin + 70, y, 20, 7, mission.frais_participation_nombre, { align: 'center', middle: true });
        drawCell(margin + 90, y, 30, 7, fmt(mission.frais_participation_unitaire), { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.frais_participation_total), { align: 'right', middle: true });
        y += 7;
        drawCell(margin, y, 70, 7, 'FRAIS DE VISA', { middle: true });
        drawCell(margin + 70, y, 20, 7, mission.frais_visa_nombre, { align: 'center', middle: true });
        drawCell(margin + 90, y, 30, 7, fmt(mission.frais_visa_unitaire), { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.frais_visa_total), { align: 'right', middle: true });
        y += 7;
        drawCell(margin, y, 120, 7, 'SOUS TOTAL 2', { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.montant_autres_frais), { align: 'right', middle: true });
        y += 10;

        drawCell(margin, y, 100, 7, "III-   BILLETS D'AVION", { middle: true });
        y += 7;
        drawCell(margin, y, 70, 7, 'CLASSE AFFAIRE', { middle: true });
        drawCell(margin + 70, y, 20, 7, mission.billets_affaire_nombre, { align: 'center', middle: true });
        drawCell(margin + 90, y, 30, 7, fmt(mission.billets_affaire_unitaire), { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.billets_affaire_total), { align: 'right', middle: true });
        y += 7;
        drawCell(margin, y, 70, 7, 'CLASSE ECONOMIQUE', { middle: true });
        drawCell(margin + 70, y, 20, 7, mission.billets_economique_nombre, { align: 'center', middle: true });
        drawCell(margin + 90, y, 30, 7, fmt(mission.billets_economique_unitaire), { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.billets_economique_total), { align: 'right', middle: true });
        y += 7;
        drawCell(margin, y, 120, 7, 'SOUS TOTAL 3', { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.montant_billets), { align: 'right', middle: true });
        y += 7;
        drawCell(margin, y, 120, 7, 'TOTAL', { align: 'right', middle: true });
        drawCell(margin + 120, y, 30, 7, fmt(mission.montant_total), { align: 'right', middle: true });
        y += 10;

        drawCell(margin, y, fullW, 8, `ARRETE A LA SOMME DE : ${mission.montant_total_en_lettres}`, { middle: true });
        y += 14;

        doc.text(`${mission.lieu_signature}, le ${mission.date_document}`, pageW - margin, y, { align: 'right' });
        y += 10;

        const signataires = Array.isArray(mission.signataires) ? mission.signataires : [];
        const blockWidth = fullW / Math.max(1, signataires.length || 1);
        signataires.forEach((signataire, index) => {
            const centerX = margin + (blockWidth * index) + blockWidth / 2;
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(9);
            doc.text(String(signataire.libelle || ''), centerX, y, { align: 'center' });
            doc.setFontSize(10);
            doc.text(String(signataire.nom || ''), centerX, y + 25, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            const fonction = doc.splitTextToSize(String(signataire.fonction || ''), blockWidth - 8);
            doc.text(fonction, centerX, y + 31, { align: 'center' });
        });

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
