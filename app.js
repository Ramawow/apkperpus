$(document).ready(function() {
    function loadPeminjam() {
        $.get('peminjam.php', function(data) {
            let peminjam = JSON.parse(data);
            $('#daftar-peminjam').empty();
            $('#transaksi_peminjam').empty();
            peminjam.forEach(function(p) {
                $('#daftar-peminjam').append('<li>' + p.nama_peminjam + '</li>');
                $('#transaksi_peminjam').append('<option value="' + p.kd_peminjam + '">' + p.nama_peminjam + '</option>');
            });
        });
    }

    function loadBuku() {
        $.get('buku.php', function(data) {
            let buku = JSON.parse(data);
            $('#daftar-buku').empty();
            $('.transaksi_buku').empty();
            buku.forEach(function(b) {
                $('#daftar-buku').append('<li>' + b.judul_buku + '</li>');
                $('.transaksi_buku').append('<option value="' + b.kd_buku + '">' + b.judul_buku + '</option>');
            });
        });
    }

    function loadHistory() {
        $.get('transaksi.php', function(data) {
            let history = JSON.parse(data);
            $('#history-peminjaman').empty();
            history.forEach(function(h) {
                $('#history-peminjaman').append('<li>' + h.nama_peminjam + ' meminjam ' + h.judul_buku + ' (' + h.jumlah + ' buku) pada ' + h.tgl_peminjaman + '</li>');
            });
        });
    }

    $('#form-peminjam').submit(function(e) {
        e.preventDefault();
        $.post('peminjam.php', {
            nama_peminjam: $('#nama_peminjam').val(),
            alamat: $('#alamat').val(),
            no_telpon: $('#no_telpon').val()
        }, function(data) {
            alert(data);
            loadPeminjam();
        });
    });

    $('#form-buku').submit(function(e) {
        e.preventDefault();
        $.post('buku.php', {
            judul_buku: $('#judul_buku').val(),
            nama_pengarang: $('#nama_pengarang').val(),
            nama_penerbit: $('#nama_penerbit').val(),
            tahun_terbit: $('#tahun_terbit').val(),
            jumlah_buku: $('#jumlah_buku').val()
        }, function(data) {
            alert(data);
            loadBuku();
        });
    });

    $('#form-transaksi').submit(function(e) {
        e.preventDefault();
        let buku = [];
        $('#buku-list .transaksi_buku').each(function(index, element) {
            buku.push({
                kd_buku: $(element).val(),
                jumlah: $(element).next('.transaksi_jumlah').val()
            });
        });
        $.post('transaksi.php', {
            kd_peminjam: $('#transaksi_peminjam').val(),
            tgl_kembali: $('#tgl_kembali').val(),
            buku: buku
        }, function(data) {
            alert(data);
            loadHistory();
        });
    });

    $('#add-buku').click(function() {
        $('#buku-list').append('<div><select class="transaksi_buku"></select><input type="number" class="transaksi_jumlah" placeholder="Jumlah"></div>');
        loadBuku();
    });

    loadPeminjam();
    loadBuku();
    loadHistory();
});
