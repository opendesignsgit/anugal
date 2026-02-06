# Anugal - Scroll-Based Hero Section

A visually engaging React.js website featuring a scroll-based hero section with smooth GSAP animations. The hero section transforms dynamically as users scroll, creating an immersive and interactive experience.

## Features

- **Scroll-Based Animations**: The hero section uses GSAP ScrollTrigger to create smooth, scroll-driven animations
- **Dynamic Layout Transformations**: Elements reposition and scale based on scroll progress
- **Responsive Design**: Optimized for both desktop and mobile devices
- **Modern Tech Stack**: Built with React, Vite, and GSAP

## Hero Section Behavior

### Initial State (Page Load)
- Large, centered logo and navigation menu
- Prominent headline: "Transform Your Digital Experience"
- Centered descriptive text
- Dashboard mockup image prominently displayed
- Beautiful gradient background with curved design

### Scroll Progression
1. **Early Scroll**: Logo shrinks and moves to top-left; menu compacts into navigation bar
2. **Mid Scroll**: Headline reduces in size and shifts left; text content repositions
3. **Late Scroll**: Dashboard image slides right and scales down; final side-by-side layout
4. **Animation Complete**: Hero section stops animating; normal scrolling resumes

## Getting Started

### Installation

```bash
npm install
```

### Development

```bash
npm run dev
```

The development server will start at `http://localhost:5173/`

### Build

```bash
npm run build
```

### Preview Production Build

```bash
npm run preview
```

## Technology Stack

- **React** - UI library
- **Vite** - Build tool and dev server
- **GSAP** - Animation library with ScrollTrigger plugin
- **CSS3** - Styling with responsive design

## Project Structure

```
anugal/
├── src/
│   ├── components/
│   │   ├── Hero.jsx       # Main hero component with scroll animations
│   │   └── Hero.css       # Hero component styles
│   ├── App.jsx            # Root application component
│   ├── App.css            # Global app styles
│   ├── index.css          # Base styles and CSS reset
│   └── main.jsx           # Application entry point
├── public/                # Static assets
├── index.html             # HTML template
└── package.json           # Dependencies and scripts
```

## Animation Details

The scroll-based animations are controlled by GSAP's ScrollTrigger plugin with the following characteristics:

- **Pin Duration**: Hero section remains pinned for 200% of viewport height
- **Scrub**: 1-second smooth catch-up for responsive feel
- **Progressive Transformations**: Staggered animations create depth
- **Responsive**: Animations adapt to different screen sizes

## Browser Compatibility

- Modern browsers with ES6+ support
- Chrome, Firefox, Safari, Edge (latest versions)

## React + Vite

This template provides a minimal setup to get React working in Vite with HMR and some ESLint rules.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) uses [Babel](https://babeljs.io/) (or [oxc](https://oxc.rs) when used in [rolldown-vite](https://vite.dev/guide/rolldown)) for Fast Refresh
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) uses [SWC](https://swc.rs/) for Fast Refresh
