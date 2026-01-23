# Anugal

Next-Gen Identity & Access Orchestration Platform - Landing Page

## Overview

This is the landing page for Anugal, featuring a scroll-based morphing hero section that creates an engaging user experience. The page transitions from a bold, centered hero layout into a standard layout with sticky navigation as users scroll.

## Features

- ✨ Scroll-based morphing hero section
- 🎨 Modern, responsive design
- 🚀 Built with React + TypeScript + Vite
- 💅 Custom CSS animations
- 📱 Mobile-friendly responsive layout
- ⚡ Optimized performance with GPU-accelerated animations

## Getting Started

### Prerequisites

- Node.js 18+ and npm

### Installation

```bash
npm install
```

### Development

Start the development server:

```bash
npm run dev
```

Visit [http://localhost:5173](http://localhost:5173) to view the application.

### Build

Build for production:

```bash
npm run build
```

### Preview Production Build

```bash
npm run preview
```

## Project Structure

```
anugal/
├── src/
│   ├── components/
│   │   ├── HeroMorph.tsx       # Main hero component with scroll morphing
│   │   └── HeroMorph.css       # Component styles
│   ├── hooks/
│   │   └── useScrollProgress.ts # Custom hook for scroll tracking
│   ├── App.tsx                  # Main app component
│   ├── main.tsx                 # Entry point
│   └── index.css                # Global styles
├── public/                      # Static assets
└── index.html                   # HTML template
```

## Customization

See [COMPONENT_README.md](./COMPONENT_README.md) for detailed information on customizing the hero component, including:
- Adjusting scroll animation timing
- Changing content and styling
- Modifying animation phases
- Swapping images and logos

## Technology Stack

- **React 19** - UI library
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server
- **CSS3** - Styling with modern features

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## License

Copyright © 2024 Anugal. All rights reserved.

---

## React + TypeScript + Vite Template Info

This template provides a minimal setup to get React working in Vite with HMR and some ESLint rules.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) uses [Babel](https://babeljs.io/) (or [oxc](https://oxc.rs) when used in [rolldown-vite](https://vite.dev/guide/rolldown)) for Fast Refresh
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) uses [SWC](https://swc.rs/) for Fast Refresh

