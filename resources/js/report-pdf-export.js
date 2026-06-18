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
