const form = document.getElementById("tambahBukuForm");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const token = localStorage.getItem("token");

    const data = {
        judul_buku: document.getElementById("judul_buku").value,
        penulis: document.getElementById("penulis").value,
        tahun_terbit: document.getElementById("tahun_terbit").value,
        kategori: document.getElementById("kategori").value,
        isbn: document.getElementById("isbn").value,
        penerbit: document.getElementById("penerbit").value,
        jumlah_halaman: document.getElementById("jumlah_halaman").value,
        stok_awal: document.getElementById("stok_awal").value,
        sinopsis: document.getElementById("sinopsis").value
    };

    try {

        const response = await fetch("http://localhost:3000/buku", {

            method: "POST",

            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`
            },

            body: JSON.stringify(data)

        });

        const result = await response.json();

        if(response.ok){

            alert("Buku berhasil ditambahkan");

            form.reset();

        }else{

            alert(result.message);

        }

    } catch(err){

        console.log(err);

        alert("Gagal terhubung ke server");

    }

});