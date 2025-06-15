// Mengambil elemen-elemen DOM yang dibutuhkan
const navbarnav = document.querySelector(".nav");
const hamburgerMenuButton = document.querySelector("#hamburger-menu");
const navLinks = document.querySelectorAll(".nav a");
const searchForm = document.querySelector(".search-form");
const searchBox = document.querySelector("#search-box");
const searchToggleButton = document.querySelector(".search-button");
const searchSubmitButton = document.querySelector("#search-btn");
const autocompleteList = document.querySelector("#autocomplete-list");

// Mengambil semua kartu kelas yang ada di halaman
const classCards = document.querySelectorAll(".class-card a");

// Array untuk menyimpan data kelas (judul dan deskripsi)
// Ini akan digunakan untuk fitur autocomplete dan live search
const classData = Array.from(classCards).map((card) => {
  return {
    element: card, // Referensi ke elemen DOM kartu
    title: card.querySelector(".card-content h1").textContent.toLowerCase(),
    description: card
      .querySelector(".card-content p")
      .textContent.toLowerCase(),
  };
});

// ---
// Fungsionalitas Hamburger Menu
// ---

// Toggle (menampilkan/menyembunyikan) hamburger menu saat diklik
hamburgerMenuButton.addEventListener("click", (e) => {
  navbarnav.classList.toggle("active");
  // Mencegah perilaku default link '#' yang membuat halaman melompat ke atas
  e.preventDefault();
});

// Menutup hamburger menu saat mengklik di luar area menu atau tombol hamburger
document.addEventListener("click", (e) => {
  // Pastikan klik tidak terjadi pada tombol hamburger itu sendiri
  // dan klik tidak terjadi di dalam area navigasi (menu)
  if (
    !hamburgerMenuButton.contains(e.target) &&
    !navbarnav.contains(e.target)
  ) {
    navbarnav.classList.remove("active");
  }
});

// Menutup hamburger menu saat mengklik salah satu link navigasi
navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    navbarnav.classList.remove("active");
    // Karena link navigasi mengarah ke ID section, tidak perlu e.preventDefault()
  });
});

// ---
// Fungsionalitas Search Form
// ---

// Toggle (menampilkan/menyembunyikan) search form saat tombol kaca pembesar di navbar diklik
searchToggleButton.addEventListener("click", (e) => {
  searchForm.classList.toggle("active");
  // Fokuskan kursor pada input pencarian segera setelah form muncul
  searchBox.focus();
  // Mencegah perilaku default link '#'
  e.preventDefault();
});

// Menangani klik pada tombol pencarian di dalam form (ikon kaca pembesar)
searchSubmitButton.addEventListener("click", (e) => {
  // Mencegah perilaku default link '#' pada label atau link
  e.preventDefault();
  // Panggil fungsi live search dengan nilai saat ini di searchBox
  filterClassCards(searchBox.value, true); // Teruskan 'true' untuk memicu scroll
  // Sembunyikan form pencarian setelah menekan tombol submit
  searchForm.classList.remove("active");
  autocompleteList.innerHTML = ""; // Kosongkan daftar autocomplete
});

// Menutup search form saat mengklik di luar area search form, tombol toggle, atau autocomplete list
document.addEventListener("click", (e) => {
  if (
    !searchToggleButton.contains(e.target) &&
    !searchForm.contains(e.target) &&
    !autocompleteList.contains(e.target) // Termasuk autocomplete list
  ) {
    searchForm.classList.remove("active");
    autocompleteList.innerHTML = ""; // Kosongkan daftar autocomplete saat ditutup
  }
});

// ---
// Fungsionalitas Autocomplete dan Live Search
// ---

// Fungsi untuk menampilkan saran autocomplete
function showAutocompleteSuggestions(searchTerm) {
  autocompleteList.innerHTML = ""; // Kosongkan daftar saran sebelumnya

  if (searchTerm.length === 0) {
    autocompleteList.style.display = "none"; // Sembunyikan jika input kosong
    return;
  }

  // Filter saran HANYA berdasarkan judul (h1)
  const filteredSuggestions = classData.filter((item) =>
    item.title.includes(searchTerm)
  );

  filteredSuggestions.forEach((item) => {
    const suggestionItem = document.createElement("div");
    suggestionItem.classList.add("autocomplete-item");
    suggestionItem.textContent = item.title; // Tampilkan judul kelas sebagai saran

    // Saat saran diklik, isi searchBox dan picu pencarian/filter
    suggestionItem.addEventListener("click", () => {
      searchBox.value = item.title; // Isi searchBox dengan judul yang dipilih
      filterClassCards(item.title, true); // Picu live search dan scroll
      searchForm.classList.remove("active"); // Tutup form
      autocompleteList.innerHTML = ""; // Kosongkan daftar
    });
    autocompleteList.appendChild(suggestionItem);
  });

  // Tampilkan autocompleteList hanya jika ada saran
  if (filteredSuggestions.length > 0) {
    autocompleteList.style.display = "block";
  } else {
    autocompleteList.style.display = "none";
  }
}

// Fungsi untuk memfilter kartu kelas (Live Search)
// Parameter `shouldScroll` ditambahkan untuk mengontrol perilaku scroll
function filterClassCards(searchTerm, shouldScroll = false) {
  const term = searchTerm.toLowerCase(); // Pastikan term pencarian huruf kecil

  classData.forEach((item) => {
    // Jika judul atau deskripsi mengandung term, tampilkan kartu, jika tidak, sembunyikan
    if (item.title.includes(term) || item.description.includes(term)) {
      item.element.style.display = "flex"; // Sesuaikan dengan display CSS kartu Anda (misal: "block", "grid", dll.)
    } else {
      item.element.style.display = "none";
    }
  });

  // Gulirkan ke bagian "Class" HANYA jika shouldScroll adalah true
  if (shouldScroll) {
    const classSection = document.getElementById("class");
    if (classSection) {
      classSection.scrollIntoView({ behavior: "smooth" });
    }
  }
}

// Event listener untuk input pada search box (untuk autocomplete dan live search)
searchBox.addEventListener("input", () => {
  const searchTerm = searchBox.value.toLowerCase();
  showAutocompleteSuggestions(searchTerm); // Tampilkan saran autocomplete
  filterClassCards(searchTerm, false); // Lakukan live search, tapi JANGAN scroll
});

// Sembunyikan autocomplete list saat search box kehilangan fokus (tapi masih dalam form)
searchBox.addEventListener("blur", () => {
  // Beri sedikit jeda agar klik pada saran autocomplete bisa terdeteksi
  setTimeout(() => {
    if (!autocompleteList.contains(document.activeElement)) {
      autocompleteList.innerHTML = ""; // Kosongkan saran jika fokus hilang
      autocompleteList.style.display = "none"; // Pastikan disembunyikan
    }
  }, 100);
});

// Pastikan autocomplete list muncul di atas elemen lain saat aktif
// Anda perlu menambahkan CSS untuk #autocomplete-list agar memiliki z-index yang tinggi
