export const processIcon = (icon) => {
  if (!icon) return '';

  // Si ya es una URL (empieza con /, http, o data:)
  if (icon.startsWith('/') || icon.startsWith('http') || icon.startsWith('data:')) {
    return icon;
  }
  
  // Si es un string SVG
  if (icon.trim().startsWith('<svg')) {
    try {
      // Convertir a data URL
      return `data:image/svg+xml;base64,${btoa(icon)}`;
    } catch (e) {
      console.error('Error convirtiendo SVG:', e);
      return '';
    }
  }

  // Si está escapado (&lt;svg)
  if (icon.includes('&lt;svg')) {
    try {
      // Desescapar
      const decoded = icon
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&amp;/g, '&');
      
      if (decoded.trim().startsWith('<svg')) {
        return `data:image/svg+xml;base64,${btoa(decoded)}`;
      }
    } catch (e) {
      console.error('Error procesando SVG escapado:', e);
      return '';
    }
  }
  
  return icon;
};

export const getSafeIconPath = (node) => {
  return node?.data?.completo?.categoria?.RutaIcono || '';
};