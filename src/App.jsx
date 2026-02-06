import { useLayoutEffect, useRef } from 'react'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import './App.css'

// Register ScrollTrigger plugin
gsap.registerPlugin(ScrollTrigger)

function App() {
  // Refs for all animated elements
  const containerRef = useRef(null)
  const headerRef = useRef(null)
  const logoRef = useRef(null)
  const menuRef = useRef(null)
  const heroTextRef = useRef(null)
  const contentRef = useRef(null)

  useLayoutEffect(() => {
    // Create a GSAP context for clean scoping
    const ctx = gsap.context(() => {
      // Helper function to get viewport-relative values
      const getViewportValues = () => ({
        vw: containerRef.current.offsetWidth,
        vh: containerRef.current.offsetHeight
      })

      // Create the main timeline with ScrollTrigger
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: containerRef.current,
          start: 'top top',
          end: '+=100%', // Pin for approximately 100vh
          pin: true,
          scrub: 1, // Smooth scrubbing
          markers: false, // Set to true for debugging
          invalidateOnRefresh: true, // Recalculate values on resize
        }
      })

      // Logo animation: large centered → small top-left
      tl.to(logoRef.current, {
        scale: 0.4,
        x: () => -getViewportValues().vw * 0.35, // Move to left
        y: () => -getViewportValues().vh * 0.35, // Move to top
        duration: 1,
      }, 0)

      // Header padding collapse
      tl.to(headerRef.current, {
        paddingTop: '1rem',
        paddingBottom: '1rem',
        duration: 1,
      }, 0)

      // Menu adjustments: position and spacing
      tl.to(menuRef.current, {
        x: () => getViewportValues().vw * 0.2, // Move to right
        y: () => -getViewportValues().vh * 0.35, // Move to top
        scale: 0.9,
        duration: 1,
      }, 0)

      // Hero headline: decrease scale and move upwards
      tl.to(heroTextRef.current, {
        scale: 0.7,
        y: -100,
        opacity: 0.5,
        duration: 1,
      }, 0)

      // Content fade-in and slide-up
      tl.fromTo(contentRef.current, 
        {
          opacity: 0,
          y: 100,
        },
        {
          opacity: 1,
          y: 0,
          duration: 0.5,
        },
        0.7 // Start slightly before the end
      )
    }, containerRef)

    // Cleanup function
    return () => {
      ctx.revert() // This will kill all ScrollTrigger instances
    }
  }, [])

  return (
    <div className="app">
      {/* Pinned container with header and hero */}
      <div ref={containerRef} className="scroll-container">
        <header ref={headerRef} className="header">
          <div ref={logoRef} className="logo">
            <h1>ANUGAL</h1>
          </div>
          <nav ref={menuRef} className="menu">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#services">Services</a>
            <a href="#contact">Contact</a>
          </nav>
        </header>

        <div ref={heroTextRef} className="hero-text">
          <h2>Create Amazing</h2>
          <h2>Experiences</h2>
          <p>Scroll to explore our scroll-driven animation</p>
        </div>

        <div ref={contentRef} className="first-content">
          <h3>Welcome to Our World</h3>
          <p>This section fades in as you scroll down</p>
        </div>
      </div>

      {/* Rest of the page content */}
      <section className="content-section">
        <div className="content-wrapper">
          <h2>Our Story</h2>
          <p>
            This is where the rest of your content begins. The header is now unpinned
            and the page scrolls naturally from here.
          </p>
          <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
            consequat.
          </p>
        </div>
      </section>

      <section className="content-section alt">
        <div className="content-wrapper">
          <h2>What We Do</h2>
          <p>
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore
            eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
            sunt in culpa qui officia deserunt mollit anim id est laborum.
          </p>
        </div>
      </section>

      <section className="content-section">
        <div className="content-wrapper">
          <h2>Get In Touch</h2>
          <p>
            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium
            doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore
            veritatis et quasi architecto beatae vitae dicta sunt explicabo.
          </p>
        </div>
      </section>
    </div>
  )
}

export default App
