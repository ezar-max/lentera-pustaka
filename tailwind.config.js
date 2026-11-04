/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                forest: {
                    DEFAULT: '#183B32',
                    hover: '#214E42',
                    dark: '#112A23',
                    soft: '#E4EDE9',
                },
                gold: {
                    DEFAULT: '#D9A441',
                    hover: '#C49030',
                    soft: '#F9F1DF',
                },
                ivory: {
                    DEFAULT: '#F7F3E8',
                    dark: '#EDE7D7',
                    paper: '#FCFBF7',
                },
                sage: {
                    DEFAULT: '#6B7C70',
                    light: '#94A398',
                    muted: '#DCE2DD',
                },
                charcoal: {
                    DEFAULT: '#292D2A',
                    light: '#454B46',
                },
                status: {
                    diajukan: '#F4EBD3',
                    'diajukan-text': '#8B651B',
                    disetujui: '#E0ECE7',
                    'disetujui-text': '#183B32',
                    dipinjam: '#E3EAEF',
                    'dipinjam-text': '#2A4B63',
                    kembali: '#EAE4F0',
                    'kembali-text': '#4D3869',
                    selesai: '#E4EBE5',
                    'selesai-text': '#295238',
                    ditolak: '#F3E3E1',
                    'ditolak-text': '#87332A',
                    dibatalkan: '#EAEAE7',
                    'dibatalkan-text': '#5A605C',
                },
            },
            borderRadius: {
                card: '15px',
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                heading: ['Fraunces', 'Georgia', 'ui-serif', 'serif'],
            },
        },
    },
    plugins: [],
};

