import { useState, useEffect, useRef } from 'react';

/**
 * Custom hook to track scroll progress within a specific section
 * Returns a normalized value (0 to 1) representing how far the user has scrolled
 * through the designated hero section height.
 */
export const useScrollProgress = (scrollHeight: number = 1000) => {
  const [progress, setProgress] = useState(0);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleScroll = () => {
      if (!containerRef.current) return;

      const scrolled = window.scrollY;
      
      // Calculate progress: 0 when at top, 1 when scrolled through scrollHeight
      const calculatedProgress = Math.min(Math.max(scrolled / scrollHeight, 0), 1);
      setProgress(calculatedProgress);
    };

    // Use passive listener for better scroll performance
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll(); // Initial calculation

    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, [scrollHeight]);

  return { progress, containerRef };
};
