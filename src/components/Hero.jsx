import { useEffect, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import './Hero.css';

gsap.registerPlugin(ScrollTrigger);

const Hero = () => {
  const heroRef = useRef(null);
  const logoRef = useRef(null);
  const menuRef = useRef(null);
  const headlineRef = useRef(null);
  const imageRef = useRef(null);
  const textContentRef = useRef(null);
  const curveRef = useRef(null);

  useEffect(() => {
    const ctx = gsap.context(() => {
      // Create a timeline for all animations
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: heroRef.current,
          start: 'top top',
          end: '+=200%', // Animation happens over 200% of viewport height
          scrub: 1, // Smooth scrubbing, takes 1 second to "catch up"
          pin: true, // Pin the hero section during scroll
          anticipatePin: 1,
        },
      });

      // Early scroll: Logo shrinks and moves to top-left
      tl.to(logoRef.current, {
        scale: 0.5,
        x: -window.innerWidth * 0.35,
        y: -window.innerHeight * 0.4,
        duration: 0.3,
      }, 0);

      // Early scroll: Menu compacts and becomes navigation bar
      tl.to(menuRef.current, {
        x: window.innerWidth * 0.15,
        y: -window.innerHeight * 0.4,
        scale: 0.9,
        duration: 0.3,
      }, 0);

      // Midway: Headline reduces and shifts left
      tl.to(headlineRef.current, {
        scale: 0.6,
        x: -window.innerWidth * 0.25,
        y: window.innerHeight * 0.05,
        duration: 0.4,
      }, 0.2);

      // Midway: Text content becomes left-aligned
      tl.to(textContentRef.current, {
        x: -window.innerWidth * 0.25,
        y: window.innerHeight * 0.2,
        scale: 0.9,
        duration: 0.4,
      }, 0.3);

      // Late scroll: Dashboard image slides right and scales down
      tl.to(imageRef.current, {
        x: window.innerWidth * 0.15,
        y: window.innerHeight * 0.1,
        scale: 0.7,
        duration: 0.4,
      }, 0.4);

      // Fade out curve background
      tl.to(curveRef.current, {
        opacity: 0.3,
        duration: 0.5,
      }, 0.3);
    }, heroRef);

    return () => ctx.revert(); // Cleanup
  }, []);

  return (
    <>
      <div className="hero-section" ref={heroRef}>
        {/* Curved background */}
        <div className="curve-background" ref={curveRef}></div>

        {/* Logo */}
        <div className="logo" ref={logoRef}>
          <h2>ANUGAL</h2>
        </div>

        {/* Menu */}
        <nav className="menu" ref={menuRef}>
          <a href="#features">Features</a>
          <a href="#about">About</a>
          <a href="#contact">Contact</a>
        </nav>

        {/* Headline */}
        <h1 className="headline" ref={headlineRef}>
          Transform Your Digital Experience
        </h1>

        {/* Text Content */}
        <p className="text-content" ref={textContentRef}>
          Discover innovative solutions that bring your ideas to life with cutting-edge technology and beautiful design.
        </p>

        {/* Dashboard Image */}
        <div className="dashboard-image" ref={imageRef}>
          <div className="mockup-dashboard">
            <div className="dashboard-header"></div>
            <div className="dashboard-content">
              <div className="card"></div>
              <div className="card"></div>
              <div className="card"></div>
            </div>
          </div>
        </div>
      </div>

      {/* Content section after hero */}
      <div className="content-section">
        <div className="content-container">
          <h2>Features Section</h2>
          <p>This is where your main content begins. The hero animation is complete, and normal scrolling continues.</p>
          
          <div className="feature-grid">
            <div className="feature-card">
              <h3>Feature 1</h3>
              <p>Amazing capability that sets you apart</p>
            </div>
            <div className="feature-card">
              <h3>Feature 2</h3>
              <p>Innovative solutions for modern challenges</p>
            </div>
            <div className="feature-card">
              <h3>Feature 3</h3>
              <p>Seamless integration and user experience</p>
            </div>
          </div>

          <h2 id="about">About Section</h2>
          <p>More content here to demonstrate normal scrolling behavior after the hero animation completes.</p>
          
          <h2 id="contact">Contact Section</h2>
          <p>Get in touch with us for more information.</p>
        </div>
      </div>
    </>
  );
};

export default Hero;
