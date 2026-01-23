# Anugal - Scroll-Driven Animation Demo

A React application demonstrating a full-page scroll-driven layout animation using GSAP ScrollTrigger. This project showcases smooth, performant animations that respond to user scroll input.

## Features

### Scroll-Driven Animations
- **Logo Transition**: Large centered logo smoothly transitions to a smaller size positioned at the top-left
- **Header Collapse**: Header padding dynamically reduces during scroll
- **Menu Adjustments**: Navigation menu repositions and scales with scroll progress
- **Hero Text Animation**: Hero headline scales down and moves upward with opacity fade
- **Content Fade-In**: First content section fades in and slides up as the animation completes

### Technical Highlights
- ✅ Built with React 19 and Vite
- ✅ GSAP ScrollTrigger for scroll-driven animations
- ✅ GPU-accelerated animations (transform and opacity only)
- ✅ Proper cleanup to prevent memory leaks
- ✅ Responsive viewport handling
- ✅ Clean component lifecycle management with `useLayoutEffect`
- ✅ No React state used in animation logic for optimal performance

## Getting Started

### Prerequisites
- Node.js 16+ and npm

### Installation

1. Clone the repository:
```bash
git clone https://github.com/opendesignsgit/anugal.git
cd anugal
```

2. Install dependencies:
```bash
npm install
```

3. Start the development server:
```bash
npm run dev
```

4. Open your browser and navigate to `http://localhost:5173`

### Build for Production

```bash
npm run build
```

The optimized build will be in the `dist` directory.

### Preview Production Build

```bash
npm run preview
```

## Project Structure

```
anugal/
├── src/
│   ├── App.jsx          # Main component with scroll animations
│   ├── App.css          # Styling for the application
│   ├── index.css        # Global styles
│   └── main.jsx         # Application entry point
├── public/              # Static assets
├── index.html           # HTML template
├── package.json         # Dependencies and scripts
└── vite.config.js       # Vite configuration
```

## How It Works

### Animation Setup

The application uses GSAP's ScrollTrigger to create a timeline that responds to scroll:

1. **Pinning**: The hero section is pinned for approximately 100vh of scroll
2. **Scrubbing**: Animations scrub smoothly with scroll progress
3. **Timeline**: All animations are coordinated on a single timeline
4. **Cleanup**: ScrollTrigger instances are properly cleaned up on unmount

### Performance Optimization

- All animations use only `transform` and `opacity` for GPU acceleration
- Direct element targeting with refs (no React state in animation logic)
- `invalidateOnRefresh` ensures animations recalculate on viewport resize
- Function-based values for responsive viewport-relative positioning

## Screenshots

### Initial State
![Initial State](https://github.com/user-attachments/assets/cd1b6320-f565-450b-84bf-f56f18b97612)
*Large centered logo with menu and hero text*

### Mid-Scroll Animation
![Mid-Scroll](https://github.com/user-attachments/assets/dcf765bb-9e21-4a33-86a5-87049d24cda7)
*Elements transitioning smoothly as user scrolls*

### End State
![End State](https://github.com/user-attachments/assets/c62ef8be-a47d-4603-836c-152646af0c36)
*Content section fades in as the page unpins*

## Technologies Used

- **React 19.2.0** - UI library
- **GSAP 3.x** - Animation library
- **Vite 7.x** - Build tool and dev server
- **ESLint** - Code linting

## Development

### Linting
```bash
npm run lint
```

### Code Quality
The project follows best practices:
- No direct window dimension access in animations
- Responsive viewport handling with function-based values
- Proper cleanup of GSAP contexts and ScrollTrigger instances
- ESLint configuration for React and hooks

## Browser Support

Modern browsers that support:
- ES6+ JavaScript
- CSS Grid and Flexbox
- CSS transforms and transitions

## License

This project is open source and available under the MIT License.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Acknowledgments

- GSAP for the amazing animation library
- React team for the excellent UI framework
- Vite for the blazing-fast build tool
