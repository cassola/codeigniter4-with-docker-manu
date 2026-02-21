(function () {
  const inputs = document.querySelectorAll('input[type="text"], textarea');
  inputs.forEach((el) => {
    el.addEventListener('focus', () => el.parentElement?.classList.add('focused'));
    el.addEventListener('blur', () => el.parentElement?.classList.remove('focused'));
  });
})();
