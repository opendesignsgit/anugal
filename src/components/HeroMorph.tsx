import type { CSSProperties } from 'react';
import { useScrollProgress } from '../hooks/useScrollProgress';
import './HeroMorph.css';

/**
 * HeroMorph Component
 * 
 * Implements a scroll-based morphing hero section that transitions from:
 * - Initial: Bold centered hero layout with large logo, centered headline, and centered image
 * - Final: Standard layout with compact navbar, left-aligned text, right-aligned image
 * 
 * Scroll Animation Phases:
 * 1. Header transforms (0.0 - 0.4): Logo shrinks, nav compacts, background changes
 * 2. Hero text settles (0.2 - 0.6): Headline moves left and scales down
 * 3. Image repositions (0.4 - 1.0): Dashboard image moves to right and scales
 */
export const HeroMorph = () => {
  // Track scroll progress through 1200px of scroll distance
  const { progress, containerRef } = useScrollProgress(1200);

  // Phase 1: Header transformation (0.0 - 0.4)
  const headerProgress = Math.min(progress / 0.4, 1);
  
  // Phase 2: Text transformation (0.2 - 0.6)
  const textProgress = Math.min(Math.max((progress - 0.2) / 0.4, 0), 1);
  
  // Phase 3: Image transformation (0.4 - 1.0)
  const imageProgress = Math.min(Math.max((progress - 0.4) / 0.6, 0), 1);

  // Interpolate header styles
  const headerHeight = 100 - headerProgress * 20; // 100px -> 80px
  const headerPadding = 40 - headerProgress * 20; // 40px -> 20px
  const headerBg = headerProgress > 0.3 ? 'rgba(255, 255, 255, 0.98)' : 'rgba(255, 255, 255, 0)';
  const headerShadow = headerProgress > 0.3 ? '0 2px 10px rgba(0,0,0,0.1)' : 'none';

  // Interpolate logo size
  const logoScale = 1 - headerProgress * 0.4; // 1.0 -> 0.6

  // Interpolate hero content positioning
  const heroTextAlign = textProgress > 0.5 ? 'left' : 'center';
  const heroTitleSize = 3.5 - textProgress * 1; // 3.5rem -> 2.5rem
  const heroTitleTranslateX = textProgress * -20; // 0% -> -20%

  // Interpolate image positioning
  const imageTranslateX = imageProgress * 30; // 0% -> 30%
  const imageScale = 1 - imageProgress * 0.15; // 1.0 -> 0.85

  // Calculate if header should be sticky
  const isHeaderSticky = progress > 0.3;

  const headerStyle: CSSProperties = {
    height: `${headerHeight}px`,
    padding: `0 ${headerPadding}px`,
    backgroundColor: headerBg,
    boxShadow: headerShadow,
    position: isHeaderSticky ? 'sticky' : 'relative',
    top: isHeaderSticky ? 0 : 'auto',
    zIndex: 1000,
    transition: 'background-color 0.3s ease, box-shadow 0.3s ease',
  };

  const logoStyle: CSSProperties = {
    transform: `scale(${logoScale})`,
    transformOrigin: 'left center',
  };

  const heroTitleStyle: CSSProperties = {
    fontSize: `${heroTitleSize}rem`,
    textAlign: heroTextAlign as 'left' | 'center',
    transform: `translateX(${heroTitleTranslateX}%)`,
  };

  const imageStyle: CSSProperties = {
    transform: `translateX(${imageTranslateX}%) scale(${imageScale})`,
  };

  return (
    <div ref={containerRef} className="hero-morph-container">
      {/* Header / Navigation */}
      <header className="hero-header" style={headerStyle}>
        <div className="header-content">
          <div className="logo" style={logoStyle}>
            <span className="logo-text">ANUGAL</span>
          </div>
          <nav className="nav-menu">
            <a href="#platform">PLATFORM</a>
            <a href="#solutions">SOLUTIONS</a>
            <a href="#why-anugal">WHY ANUGAL</a>
            <a href="#knowledge-hub">IGA KNOWLEDGE HUB</a>
            <a href="#resources">RESOURCES</a>
            <a href="#pricing">PRICING</a>
          </nav>
          <div className="header-actions">
            <button className="btn-secondary">REQUEST DEMO</button>
            <button className="btn-primary">BOOK A DEMO</button>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="hero-section">
        <div className="hero-background">
          <div className="bg-gradient-top"></div>
          <div className="bg-gradient-bottom"></div>
        </div>
        
        <div className="hero-content">
          <div className="hero-text">
            <h1 className="hero-title" style={heroTitleStyle}>
              Discover the Next-Gen Identity & Access Orchestration Platform
            </h1>
            <p className="hero-subtitle">
              Automate and Orchestrate access decisions with visibility, control and compliance with seamless integrations
            </p>
            <div className="hero-cta">
              <button className="btn-primary-large">Get Started</button>
              <button className="btn-secondary-large">Watch Demo</button>
            </div>
          </div>
          
          <div className="hero-image-container" style={imageStyle}>
            <div className="dashboard-image">
              {/* Placeholder for dashboard screenshot */}
              <div className="dashboard-placeholder">
                <div className="dashboard-header"></div>
                <div className="dashboard-content">
                  <div className="dashboard-sidebar"></div>
                  <div className="dashboard-main">
                    <div className="chart-row">
                      <div className="chart"></div>
                      <div className="chart"></div>
                    </div>
                    <div className="table-row"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Content Section - Normal scrolling after morph */}
      <section className="content-section">
        <div className="content-wrapper">
          <h2>Platform Features</h2>
          <div className="feature-grid">
            <div className="feature-card">
              <h3>Identity Orchestration</h3>
              <p>Seamlessly manage identities across your entire ecosystem with intelligent automation and policy-based governance.</p>
            </div>
            <div className="feature-card">
              <h3>Access Management</h3>
              <p>Control and monitor access with precision, ensuring the right people have the right permissions at the right time.</p>
            </div>
            <div className="feature-card">
              <h3>Compliance & Audit</h3>
              <p>Maintain compliance effortlessly with comprehensive audit trails and automated reporting capabilities.</p>
            </div>
            <div className="feature-card">
              <h3>Integration Hub</h3>
              <p>Connect with existing tools and systems through our extensive library of pre-built integrations.</p>
            </div>
          </div>
        </div>
      </section>

      <section className="content-section">
        <div className="content-wrapper">
          <h2>Why Choose Anugal?</h2>
          <p className="section-intro">
            Built for modern enterprises, Anugal combines cutting-edge technology with intuitive design to deliver 
            unparalleled identity and access orchestration capabilities.
          </p>
          <div className="stats-grid">
            <div className="stat-card">
              <div className="stat-number">99.9%</div>
              <div className="stat-label">Uptime SLA</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">500+</div>
              <div className="stat-label">Integrations</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">10M+</div>
              <div className="stat-label">Identities Managed</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">24/7</div>
              <div className="stat-label">Support</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};
