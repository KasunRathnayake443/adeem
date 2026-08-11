document.querySelectorAll('input[name="report_type"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.report-section').forEach(sec => {
      sec.classList.toggle('is-active', sec.dataset.section === radio.value);
    });
  });
});
