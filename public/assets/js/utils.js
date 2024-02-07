const THEME = window.matchMedia
    && window.matchMedia('(prefers-color-scheme: light)').matches
    ? 'light' : 'dark'

export { THEME }