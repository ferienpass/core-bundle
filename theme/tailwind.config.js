module.exports = {
    mode: 'jit',
    purge: [
        '../templates/Alert/*.html.twig',
        '../templates/Backend/**/*.html.twig',
        '../templates/Form/*.html.twig',
        '../contao/templates/backend/*.html5',
        './safelist.txt',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#d7edf5',
                    100: '#C7E7F2',
                    200: '#9AC8D9',
                    300: '#22A6D6',
                    400: '#1C89B0',
                    500: '#1D8DB9',
                    600: '#1c6b9c',
                    700: '#185A85',
                    800: '#084F69',
                    900: '#0C394A',
                },
                tint: '#E08B2D',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
