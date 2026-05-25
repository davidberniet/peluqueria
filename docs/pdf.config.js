module.exports = {
  stylesheet: ['pdf-style.css'],
  pdf_options: {
    format: 'A4',
    margin: { top: '20mm', right: '16mm', bottom: '18mm', left: '16mm' },
    printBackground: true,
    displayHeaderFooter: true,
    headerTemplate: '<div></div>',
    footerTemplate:
      '<div style="font-size:8px; width:100%; padding:0 16mm; color:#9ca3af; display:flex; justify-content:space-between;">' +
      '<span>Venus — Memoria Técnica · David Berlanga Nieto</span>' +
      '<span>Página <span class="pageNumber"></span> / <span class="totalPages"></span></span>' +
      '</div>',
  },
};
