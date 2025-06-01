# Movies Theme

A modern WordPress theme for movie and actor management with Sass compilation and modern development workflow.

## Features

- 🎬 Custom post types for Movies and Actors
- 🎨 Sass-based styling with modular architecture
- 📱 Responsive design with mobile-first approach
- ⚡ AJAX filtering and live search
- 💝 Wishlist functionality
- 🔧 Modern build process with npm scripts

## Development Setup

### Prerequisites

- Node.js (v16 or higher)
- npm or yarn
- WordPress development environment

### Installation

1. Navigate to the theme directory:
```bash
cd app/public/wp-content/themes/movies-theme
```

2. Install dependencies:
```bash
npm install
```

## Build Scripts

### Development Mode
```bash
# Watch for changes and compile in real-time
npm run dev
# or
npm run watch
```

### Production Build
```bash
# Build optimized assets for production
npm run build
```

### Individual Tasks
```bash
# Compile Sass to CSS
npm run build:css

# Watch Sass files only
npm run watch:css

# Minify JavaScript
npm run build:js

# Lint SCSS files
npm run lint:scss

# Lint JavaScript files
npm run lint:js

# Clean compiled assets
npm run clean
```

## File Structure

```
movies-theme/
├── assets/
│   ├── scss/               # Sass source files
│   │   ├── main.scss      # Main Sass file
│   │   ├── _variables.scss # Theme variables
│   │   ├── _mixins.scss   # Sass mixins
│   │   ├── _base.scss     # Base styles
│   │   ├── _layout.scss   # Layout styles
│   │   ├── _typography.scss # Typography
│   │   └── components/    # Component styles
│   ├── css/               # Compiled CSS (generated)
│   ├── js/                # JavaScript source files
│   └── images/            # Theme images
├── inc/                   # PHP includes
├── templates/             # Page templates
├── widgets/               # Custom widgets
├── package.json           # Node.js dependencies
└── README.md             # This file
```

## Sass Architecture

The theme uses a modular Sass architecture:

- **Variables** (`_variables.scss`): Colors, fonts, spacing, breakpoints
- **Mixins** (`_mixins.scss`): Reusable Sass mixins
- **Base** (`_base.scss`): Reset and foundational styles
- **Layout** (`_layout.scss`): Grid system and layout utilities
- **Typography** (`_typography.scss`): Text and heading styles
- **Components**: Individual component styles in `components/` directory

## JavaScript Structure

- `main.js`: Core theme functionality
- `ajax-filters.js`: AJAX filtering system
- `wishlist.js`: Wishlist functionality
- `search.js`: Live search features

## WordPress Integration

The theme includes:

- Custom post types: `movie` and `actor`
- Custom taxonomies: `genre` and `rating`
- Custom meta boxes for movie details
- AJAX handlers for dynamic functionality
- Widget areas and custom widgets
- Helper functions for theme features

## Browser Support

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)
- Internet Explorer 11

## Contributing

1. Follow WordPress coding standards
2. Use Sass for all styling
3. Lint your code before committing
4. Test on multiple devices and browsers

## License

GPL-2.0-or-later 