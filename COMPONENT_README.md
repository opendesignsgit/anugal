# HeroMorph Component

## Overview

The `HeroMorph` component implements a scroll-based morphing hero section that transitions from a bold, centered hero layout into a standard layout with a compact navbar. This creates an engaging first-screen experience that feels dynamic and modern.

## Features

- **Scroll-based Animation**: The hero section morphs smoothly as the user scrolls, with no scroll-jacking or artificial delays
- **Three-phase Transformation**:
  1. **Header Phase (0-40% progress)**: Logo shrinks, navigation compacts, background becomes opaque
  2. **Text Phase (20-60% progress)**: Headline scales down and shifts from center to left alignment
  3. **Image Phase (40-100% progress)**: Dashboard image moves to the right and scales slightly
- **Sticky Navigation**: After morphing completes, the header becomes a standard sticky navbar
- **Responsive Design**: Adapts to different screen sizes with mobile-friendly layouts
- **Performance Optimized**: Uses CSS transforms and passive scroll listeners for smooth 60fps animations

## Usage

```tsx
import { HeroMorph } from './components/HeroMorph';

function App() {
  return <HeroMorph />;
}
```

## Customization

### Adjusting Scroll Distance

The total scroll distance for the morph animation is controlled in the `useScrollProgress` hook call:

```tsx
// In HeroMorph.tsx
const { progress, containerRef } = useScrollProgress(1200); // 1200px of scroll
```

Change the value (default: 1200) to make the animation faster (lower value) or slower (higher value).

### Adjusting Animation Phases

The three phases of the animation can be customized by modifying the progress calculations:

```tsx
// Phase 1: Header transformation (currently 0.0 - 0.4)
const headerProgress = Math.min(progress / 0.4, 1);

// Phase 2: Text transformation (currently 0.2 - 0.6)
const textProgress = Math.min(Math.max((progress - 0.2) / 0.4, 0), 1);

// Phase 3: Image transformation (currently 0.4 - 1.0)
const imageProgress = Math.min(Math.max((progress - 0.4) / 0.6, 0), 1);
```

To change when a phase starts or ends:
- Adjust the start value (e.g., `progress - 0.2` where 0.2 is the start)
- Adjust the duration (e.g., `/ 0.4` where 0.4 is the duration)

### Swapping Content

#### Logo
Replace the text logo in `HeroMorph.tsx`:
```tsx
<div className="logo-text">YOUR LOGO</div>
```
Or add an image:
```tsx
<img src="/path/to/logo.svg" alt="Company Logo" />
```

#### Navigation Items
Edit the nav menu array:
```tsx
<nav className="nav-menu">
  <a href="#your-link">YOUR ITEM</a>
  {/* Add more items */}
</nav>
```

#### Hero Text
Update the headline and subtitle:
```tsx
<h1 className="hero-title" style={heroTitleStyle}>
  Your Custom Headline
</h1>
<p className="hero-subtitle">
  Your custom subtitle text
</p>
```

#### Dashboard Image
Replace the placeholder dashboard in the CSS or add a real image:
```tsx
<div className="hero-image-container" style={imageStyle}>
  <img src="/path/to/dashboard.png" alt="Dashboard" />
</div>
```

### Styling

All styles are contained in `HeroMorph.css`. Key customization points:

- **Colors**: Search for color values (e.g., `#0066cc`) and replace with your brand colors
- **Fonts**: Modify font sizes in the CSS (search for `font-size`)
- **Spacing**: Adjust padding and margins throughout the CSS
- **Background Gradient**: Edit `.bg-gradient-bottom` for the hero background curve

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Optimized with responsive breakpoints

## Performance Notes

- Uses `will-change` CSS property on animated elements to optimize rendering
- Scroll listener is passive for better performance
- Animations use `transform` and `opacity` which are GPU-accelerated
- No layout thrashing or forced reflows

## File Structure

```
src/
├── components/
│   ├── HeroMorph.tsx      # Main component
│   └── HeroMorph.css      # Styles
├── hooks/
│   └── useScrollProgress.ts # Scroll tracking hook
└── App.tsx                 # Entry point
```

## Troubleshooting

**Issue**: Animation feels too fast or slow
- **Solution**: Adjust the scroll distance value in `useScrollProgress(1200)`

**Issue**: Header becomes sticky too early/late
- **Solution**: Modify the condition in `const isHeaderSticky = progress > 0.3;`

**Issue**: Text or image transition timing feels off
- **Solution**: Adjust the phase calculation values (start and duration)

## Accessibility

- Semantic HTML structure with proper heading hierarchy
- Keyboard navigation support for all interactive elements
- Focus indicators on buttons and links
- Responsive design for screen readers

## License

Part of the Anugal project.
