document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('ajax_contact');
  const button = document.getElementById('submitBtn');
  const btnText1 = button.querySelector('.btn-text-1');
  const btnText2 = button.querySelector('.btn-text-2');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Enter sending state (force blue + white text immediately)
    button.classList.add('sending');
    button.classList.remove('sent');
    button.setAttribute('disabled', 'disabled');
    button.style.pointerEvents = 'none';
    button.style.background = '#0053b0';  // 🔵 Brand blue
    button.style.color = '#fff';          // ⚪ White text (force it)
    btnText1.style.color = '#fff';        // ensure child spans are white too
    btnText2.style.color = '#fff';
    btnText1.textContent = 'Sending...';
    btnText2.textContent = 'Sending...';

    const formData = new FormData(form);

    try {
      const response = await fetch('mail.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.status === 'success') {
        btnText1.textContent = 'Sent ✅';
        btnText2.textContent = 'Sent ✅';
        button.classList.remove('sending');
        button.classList.add('sent');

        // ✅ Reset form after short delay for smoother UX
        setTimeout(() => {
          form.reset();
        }, 500);

        // Reset button to yellow after 2 seconds
        setTimeout(() => {
          btnText1.textContent = 'Send Message';
          btnText2.textContent = 'Send Message';
          button.classList.remove('sent');
          button.removeAttribute('disabled');
          button.style.pointerEvents = 'auto';
          button.style.background = '#fed201'; // 🟡 yellow
          button.style.color = '#000';         // black text
          btnText1.style.color = '#000';
          btnText2.style.color = '#000';
        }, 2000);
      } else {
        alert(result.message || 'Something went wrong.');
        resetButton();
      }
    } catch (err) {
      alert('Error sending message.');
      resetButton();
    }

    function resetButton() {
      button.classList.remove('sending', 'sent');
      btnText1.textContent = 'Send Message';
      btnText2.textContent = 'Send Message';
      button.removeAttribute('disabled');
      button.style.pointerEvents = 'auto';
      button.style.background = '#fed201';
      button.style.color = '#000';
      btnText1.style.color = '#000';
      btnText2.style.color = '#000';
    }
  });
});
