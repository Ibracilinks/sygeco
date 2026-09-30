import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import browserslist from 'browserslist';
import { browserslistToTargets } from 'lightningcss';

// Tailwind v4 émet oklch() et color-mix(), inconnus de Chrome/Edge 109 — la dernière
// version disponible sous Windows 7/8.1 — et de Firefox 115 ESR. Sans repli, toutes
// les couleurs tombent et la mise en page part en morceaux. Lightning CSS génère les
// équivalents hex, gardés par @supports pour ne rien changer aux navigateurs récents.
const cibles = browserslistToTargets(
    browserslist('chrome >= 109, edge >= 109, firefox >= 115, safari >= 15.4'),
);

/**
 * Tailwind exprime les modificateurs d'opacité (`bg-white/95`) avec color-mix() et une
 * variable CSS : Lightning CSS ne peut pas la résoudre au build. Sur Chrome/Edge 109 la
 * déclaration est invalide et purement ignorée — les fonds disparaissent. On insère donc
 * juste avant la couleur pleine, que les navigateurs récents écrasent aussitôt.
 */
function repliColorMix() {
    const DECLARATION = /([\w-]+)\s*:\s*color-mix\(in oklab,\s*(var\(--[\w-]+\))[^;}]*\)/g;

    return {
        name: 'repli-color-mix',
        apply: 'build',
        generateBundle(_options, bundle) {
            for (const fichier of Object.values(bundle)) {
                if (fichier.type !== 'asset' || !fichier.fileName.endsWith('.css')) continue;

                fichier.source = String(fichier.source).replace(
                    DECLARATION,
                    (declaration, propriete, variable) => `${propriete}:${variable};${declaration}`,
                );
            }
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        repliColorMix(),
    ],
    css: {
        transformer: 'lightningcss',
        lightningcss: { targets: cibles },
    },
    build: {
        cssMinify: 'lightningcss',
    },
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
