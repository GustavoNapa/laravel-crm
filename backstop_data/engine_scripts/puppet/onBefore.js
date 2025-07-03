module.exports = async (page, scenario, vp) => {
  console.log('SCENARIO > ' + scenario.label);
  
  // Se não for a página de login, fazer login primeiro
  if (!scenario.url.includes('/login')) {
    console.log('Fazendo login automático...');
    
    // Navegar para página de login
    await page.goto('http://localhost:8000/admin/login');
    await page.waitForSelector('input[placeholder="Email Address"]', { timeout: 10000 });
    
    // Preencher credenciais
    await page.type('input[placeholder="Email Address"]', 'admin@example.com');
    await page.type('input[placeholder="Password"]', 'admin123');
    
    // Fazer login
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    
    console.log('Login realizado com sucesso');
  }
};

