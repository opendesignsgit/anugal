# GSAP Scroll-Driven Animation Demo

A React application demonstrating advanced scroll-driven layout animations using GSAP and ScrollTrigger.

## 🎬 Features

- **Scroll-driven animations** powered by GSAP and ScrollTrigger
- **Pinned scroll sections** that animate as you scroll
- **Performance-optimized** using only `transform` and `opacity` properties
- **Proper cleanup** with no memory leaks
- **React best practices** using hooks and refs

## 🚀 Animation Details

### First Screen Animation
The application features a hero section that:
1. **Pins** the container for approximately 100vh of scroll
2. **Animates multiple elements** simultaneously:
   - **Logo**: Scales down from 1.0 to 0.5 and moves to the top-left
   - **Header**: Collapses padding from 60px to 20px
   - **Menu**: Adjusts position and reduces spacing from 40px to 20px
   - **Hero Headline**: Scales down to 0.7 and translates upward
   - **Content Section**: Fades in and slides up near the end of the timeline
3. **Releases the pin** after animation completes for normal scrolling

### Technical Implementation

- Uses `useLayoutEffect` for synchronous DOM manipulation
- Leverages `gsap.context()` for proper cleanup and memory management
- Uses `useRef` for DOM element targeting (no React state for animations)
- Implements `ScrollTrigger` with scrubbing for smooth scroll-linked animations
- All animations use only performant properties: `transform` and `opacity`

## 📦 Installation

```bash
npm install
```

## 🛠️ Development

Start the development server:

```bash
npm run dev
```

The application will be available at `http://localhost:5173/`

## 🏗️ Build

Build for production:

```bash
npm run build
```

Preview production build:

```bash
npm run preview
```

## 📚 Technologies Used

- **React** - UI library
- **Vite** - Build tool and dev server
- **GSAP** - Animation library
- **ScrollTrigger** - GSAP plugin for scroll-driven animations

## 🎯 Key Concepts Demonstrated

1. **useLayoutEffect vs useEffect**: Using `useLayoutEffect` ensures GSAP runs before the browser paints
2. **GSAP Context**: Proper cleanup with `ctx.revert()` prevents memory leaks
3. **ScrollTrigger Pinning**: Creating immersive scroll experiences
4. **Timeline Control**: Coordinating multiple animations
5. **Performance**: Only animating `transform` and `opacity` for 60fps

## 📖 Code Structure

```
src/
├── App.jsx                    # Main app component
├── ScrollAnimationDemo.jsx    # Scroll animation implementation
├── ScrollAnimationDemo.css    # Styles for the demo
├── index.css                  # Global styles
└── main.jsx                   # App entry point
```

## 🎨 Screenshots

### Initial State
![Initial State](https://github.com/user-attachments/assets/a9e39aac-e999-4f55-8538-03be3bc4d16d)

### Mid-Scroll Animation
![Mid-Scroll](https://github.com/user-attachments/assets/2a389efd-c8ef-4a76-a38b-7c9fb4ac8a0a)

### After Animation (Normal Scroll)
![After Animation](https://github.com/user-attachments/assets/50aa94a5-c22a-42bf-ae31-8a669c5be3de)

## 📝 License

MIT
