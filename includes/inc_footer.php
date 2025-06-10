   <!-- footer start -->
     <footer>
        <section class="socials">
            <a href="https://www.instagram.com/nic.smkn1pungging/"><i class="fa-brands fa-instagram fa-lg"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin fa-lg"></i></a>
            <a href="https://discord.com/invite/afZ6p7bQwx"><i class="fa-brands fa-discord fa-lg"></i></a>

        </section>
        <ul class="nav">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#class">Class</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <section class="credits">
            <p>© 2025 NETWORK IT CLUB. All rights reserved.</p>
            <p>Developed by <a href="https://www.instagram.com/abimanyupw_/">Abimanyu Pradipa W</a></p>
        </section>
    </footer>
    <!-- footer end -->

    <!-- my javascript -->
    <script src="js/script.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', () => {
  const contactform = document.getElementById('contactform');
  const contactbtn = document.getElementById('contactButton');

  function validateForm() {
    const requiredFields = contactform.querySelectorAll('[required]');
    const allFilled = Array.from(requiredFields).every(field => field.value.trim() !== '');
    contactbtn.disabled = !allFilled;
    contactbtn.classList.toggle('disabled', !allFilled);
  }

  contactform.addEventListener('input', validateForm);
  validateForm();

  contactbtn.addEventListener('click', (e) => {
    e.preventDefault();
    if (contactbtn.disabled) return;

    const formData = new FormData(contactform);
    const dataObj = Object.fromEntries(formData.entries());

    const message = 
`Data Kontak:
Nama: ${dataObj.name}
Email: ${dataObj.email}
No HP: ${dataObj.phone}
Pesan: ${dataObj.pesan}
`;

    const waNumber = '6289509088396';
    const waUrl = `https://api.whatsapp.com/send?phone=${waNumber}&text=${encodeURIComponent(message)}`;

    window.open(waUrl, '_blank');
  });
});
</script>
</body>



</html>