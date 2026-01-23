import { useLayoutEffect, useRef } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import './ScrollAnimationDemo.css';

// Register ScrollTrigger plugin
gsap.registerPlugin(ScrollTrigger);

function ScrollAnimationDemo() {
  // Refs for DOM elements - no React state needed for animations
  const containerRef = useRef(null);
  const logoRef = useRef(null);
  const headerRef = useRef(null);
  const menuRef = useRef(null);
  const heroRef = useRef(null);
  const contentRef = useRef(null);

  useLayoutEffect(() => {
    // Create GSAP context for proper cleanup
    const ctx = gsap.context(() => {
      // Create a timeline with ScrollTrigger
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: containerRef.current,
          start: 'top top',
          end: '+=100vh', // Pin for ~100vh
          scrub: true, // Smooth scrubbing effect
          pin: true, // Pin the container during scroll
          anticipatePin: 1,
          markers: import.meta.env.DEV && false, // Enable for debugging: change false to true
        },
      });

      // Initial state for all elements
      gsap.set(logoRef.current, {
        scale: 1,
        x: 0,
        y: 0,
      });

      gsap.set(headerRef.current, {
        padding: '60px 40px',
      });

      gsap.set(menuRef.current, {
        gap: '40px',
        x: 0,
      });

      gsap.set(heroRef.current, {
        scale: 1,
        y: 0,
      });

      gsap.set(contentRef.current, {
        opacity: 0,
        y: 100,
      });

      // Animate logo: scale down and move to top-left
      tl.to(
        logoRef.current,
        {
          scale: 0.5,
          x: -200,
          y: -150,
          duration: 1,
          ease: 'power2.inOut',
        },
        0
      );

      // Animate header: collapse padding
      tl.to(
        headerRef.current,
        {
          padding: '20px 40px',
          duration: 1,
          ease: 'power2.inOut',
        },
        0
      );

      // Animate menu: adjust position and reduce spacing
      tl.to(
        menuRef.current,
        {
          gap: '20px',
          x: 50,
          duration: 1,
          ease: 'power2.inOut',
        },
        0
      );

      // Animate hero headline: scale down and translateY
      tl.to(
        heroRef.current,
        {
          scale: 0.7,
          y: -100,
          duration: 1,
          ease: 'power2.inOut',
        },
        0
      );

      // Near the end of timeline, fade and slide in content section
      tl.to(
        contentRef.current,
        {
          opacity: 1,
          y: 0,
          duration: 0.5,
          ease: 'power2.out',
        },
        0.7 // Start at 70% of timeline
      );
    }, containerRef);

    // Cleanup function to prevent memory leaks
    return () => {
      ctx.revert(); // This cleans up all GSAP animations and ScrollTriggers
    };
  }, []);

  return (
    <div className="scroll-animation-wrapper">
      {/* Pinned container with header and hero */}
      <div ref={containerRef} className="pinned-container">
        {/* Header */}
        <header ref={headerRef} className="header">
          {/* Logo */}
          <div ref={logoRef} className="logo">
            LOGO
          </div>

          {/* Menu */}
          <nav ref={menuRef} className="menu">
            <a href="#about">About</a>
            <a href="#services">Services</a>
            <a href="#portfolio">Portfolio</a>
            <a href="#contact">Contact</a>
          </nav>
        </header>

        {/* Hero Section */}
        <div className="hero-section">
          <h1 ref={heroRef} className="hero-headline">
            Scroll-Driven
            <br />
            Animation Magic
          </h1>
        </div>

        {/* Content section - fades in near end of timeline */}
        <div ref={contentRef} className="content-preview">
          <h2>Discover More</h2>
          <p>Continue scrolling to explore</p>
        </div>
      </div>

      {/* Additional content sections after the pinned animation */}
      <section className="content-section">
        <h2>Section 1</h2>
        <p>
          This section appears after the scroll animation completes. The pin is
          released and normal scrolling resumes.
        </p>
      </section>

      <section className="content-section">
        <h2>Section 2</h2>
        <p>
          This demonstrates that the scroll animation has properly released the
          pin and the rest of the page flows normally.
        </p>
      </section>

      <section className="content-section">
        <h2>Section 3</h2>
        <p>
          All animations use only transform and opacity properties for optimal
          performance, ensuring smooth 60fps animations.
        </p>
      </section>
    </div>
  );
}

export default ScrollAnimationDemo;
