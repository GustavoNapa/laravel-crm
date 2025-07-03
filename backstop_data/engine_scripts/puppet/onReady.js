module.exports = async (page, scenario, vp) => {
  console.log('READY > ' + scenario.label);
  
  // Remover elementos dinâmicos que podem causar falsos positivos
  await page.evaluate(() => {
    // Remover debug bar se existir
    const debugBar = document.querySelector('.phpdebugbar');
    if (debugBar) debugBar.remove();
    
    // Remover timestamps dinâmicos
    const timestamps = document.querySelectorAll('.timestamp, [data-timestamp]');
    timestamps.forEach(el => el.style.visibility = 'hidden');
    
    // Estabilizar animações CSS
    const style = document.createElement('style');
    style.innerHTML = `
      *, *::before, *::after {
        animation-duration: 0s !important;
        animation-delay: 0s !important;
        transition-duration: 0s !important;
        transition-delay: 0s !important;
      }
    `;
    document.head.appendChild(style);
  });
  
  // Para páginas específicas, aguardar elementos específicos
  if (scenario.label.includes('Agenda')) {
    try {
      // Aguardar calendário carregar
      await page.waitForSelector('#calendar', { timeout: 5000 });
      console.log('Calendário carregado');
    } catch (e) {
      console.log('Calendário não encontrado, continuando...');
    }
  }
};

