from pathlib import Path

from docx import Document
from docx.enum.section import WD_SECTION
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Inches, Pt, RGBColor


ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "docs" / "Panduan Video Presentasi Edira Fotocopy.docx"
LOGO = ROOT / "public" / "images" / "logoedira.png"

BLUE = "174A7E"
LIGHT_BLUE = "EAF2F8"
PALE_BLUE = "F5F9FC"
GRAY = "D9E1E8"
TEXT = RGBColor(31, 41, 55)


def set_cell_shading(cell, fill):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = tc_pr.find(qn("w:shd"))
    if shd is None:
        shd = OxmlElement("w:shd")
        tc_pr.append(shd)
    shd.set(qn("w:fill"), fill)


def set_cell_border(cell, color=GRAY, size="6"):
    tc_pr = cell._tc.get_or_add_tcPr()
    borders = tc_pr.first_child_found_in("w:tcBorders")
    if borders is None:
        borders = OxmlElement("w:tcBorders")
        tc_pr.append(borders)
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        tag = "w:" + edge
        node = borders.find(qn(tag))
        if node is None:
            node = OxmlElement(tag)
            borders.append(node)
        node.set(qn("w:val"), "single")
        node.set(qn("w:sz"), size)
        node.set(qn("w:color"), color)


def set_cell_margins(cell, top=120, start=140, bottom=120, end=140):
    tc = cell._tc
    tc_pr = tc.get_or_add_tcPr()
    tc_mar = tc_pr.first_child_found_in("w:tcMar")
    if tc_mar is None:
        tc_mar = OxmlElement("w:tcMar")
        tc_pr.append(tc_mar)
    for margin, value in (("top", top), ("start", start), ("bottom", bottom), ("end", end)):
        node = tc_mar.find(qn("w:" + margin))
        if node is None:
            node = OxmlElement("w:" + margin)
            tc_mar.append(node)
        node.set(qn("w:w"), str(value))
        node.set(qn("w:type"), "dxa")


def font_run(run, size=10.5, bold=False, color=TEXT):
    run.font.name = "Aptos"
    run._element.get_or_add_rPr().rFonts.set(qn("w:ascii"), "Aptos")
    run._element.get_or_add_rPr().rFonts.set(qn("w:hAnsi"), "Aptos")
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.color.rgb = color


def add_bullet(doc, text, level=0):
    p = doc.add_paragraph(style="List Bullet" if level == 0 else "List Bullet 2")
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = 1.08
    font_run(p.add_run(text))
    return p


def add_numbered(doc, number, text):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Cm(0.55)
    p.paragraph_format.first_line_indent = Cm(-0.55)
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = 1.08
    font_run(p.add_run(f"{number}. "), bold=True, color=RGBColor(23, 74, 126))
    font_run(p.add_run(text))
    return p


def add_label_paragraph(doc, label, text):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.12
    font_run(p.add_run(label + " "), bold=True)
    font_run(p.add_run(text))
    return p


def add_quote(doc, text):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Cm(0.65)
    p.paragraph_format.right_indent = Cm(0.4)
    p.paragraph_format.space_before = Pt(3)
    p.paragraph_format.space_after = Pt(8)
    p.paragraph_format.line_spacing = 1.12
    r = p.add_run(text)
    font_run(r, size=10)
    r.font.italic = True
    return p


def keep_with_next(paragraph):
    paragraph.paragraph_format.keep_with_next = True


def add_member_section(doc, number, name, title, duration, objective, steps, narration, handoff):
    heading = doc.add_heading(f"{number}  {name}", level=1)
    keep_with_next(heading)
    sub = doc.add_paragraph()
    sub.paragraph_format.space_after = Pt(8)
    font_run(sub.add_run(title), size=12, bold=True, color=RGBColor(23, 74, 126))
    font_run(sub.add_run(f"  |  Durasi {duration}"), size=10, color=RGBColor(75, 85, 99))

    add_label_paragraph(doc, "Tujuan bagian", objective)
    h = doc.add_heading("Urutan demonstrasi", level=2)
    keep_with_next(h)
    for step_number, step in enumerate(steps, start=1):
        add_numbered(doc, step_number, step)

    h = doc.add_heading("Contoh narasi", level=2)
    keep_with_next(h)
    for paragraph in narration:
        add_quote(doc, paragraph)

    h = doc.add_heading("Kalimat penghubung", level=2)
    keep_with_next(h)
    add_quote(doc, handoff)


doc = Document()
section = doc.sections[0]
section.top_margin = Cm(2.0)
section.bottom_margin = Cm(1.8)
section.left_margin = Cm(2.2)
section.right_margin = Cm(2.2)

styles = doc.styles
normal = styles["Normal"]
normal.font.name = "Aptos"
normal._element.rPr.rFonts.set(qn("w:ascii"), "Aptos")
normal._element.rPr.rFonts.set(qn("w:hAnsi"), "Aptos")
normal.font.size = Pt(10.5)
normal.font.color.rgb = TEXT

title_style = styles["Title"]
title_style.font.name = "Aptos Display"
title_style._element.rPr.rFonts.set(qn("w:ascii"), "Aptos Display")
title_style._element.rPr.rFonts.set(qn("w:hAnsi"), "Aptos Display")
title_style.font.size = Pt(28)
title_style.font.bold = True
title_style.font.color.rgb = RGBColor(0, 0, 0)

for style_name, size in (("Heading 1", 17), ("Heading 2", 12)):
    style = styles[style_name]
    style.font.name = "Aptos Display"
    style._element.rPr.rFonts.set(qn("w:ascii"), "Aptos Display")
    style._element.rPr.rFonts.set(qn("w:hAnsi"), "Aptos Display")
    style.font.size = Pt(size)
    style.font.bold = True
    style.font.color.rgb = RGBColor(0, 0, 0)
    style.paragraph_format.space_before = Pt(12 if style_name == "Heading 1" else 8)
    style.paragraph_format.space_after = Pt(5)

# Cover
if LOGO.exists():
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_after = Pt(26)
    p.add_run().add_picture(str(LOGO), width=Inches(3.6))

p = doc.add_paragraph(style="Title")
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.add_run("Panduan Video Presentasi\nEdira Fotocopy")

p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.paragraph_format.space_before = Pt(10)
p.paragraph_format.space_after = Pt(20)
font_run(p.add_run("Pembagian tugas dan alur demonstrasi sistem"), size=13, color=RGBColor(75, 85, 99))

team = doc.add_paragraph()
team.alignment = WD_ALIGN_PARAGRAPH.CENTER
team.paragraph_format.space_before = Pt(18)
font_run(team.add_run("Kevin  •  Kiki  •  Jonedi  •  Gandewa  •  Jericho"), size=11, bold=True, color=RGBColor(23, 74, 126))

intro = doc.add_paragraph()
intro.alignment = WD_ALIGN_PARAGRAPH.CENTER
intro.paragraph_format.left_indent = Cm(1.2)
intro.paragraph_format.right_indent = Cm(1.2)
intro.paragraph_format.space_before = Pt(22)
intro.paragraph_format.line_spacing = 1.2
font_run(intro.add_run("Dokumen ini menjadi acuan bersama agar presentasi mengikuti satu alur yang konsisten, mulai dari autentikasi dan master data hingga transaksi, pembayaran, persediaan, laporan, dan audit aktivitas."), size=10.5)

doc.add_page_break()

# Overview
doc.add_heading("Rencana Presentasi", level=1)
add_label_paragraph(doc, "Durasi yang disarankan", "15 sampai 18 menit, dengan waktu sekitar 3 sampai 4 menit untuk setiap anggota.")
add_label_paragraph(doc, "Pendekatan", "Setiap anggota melanjutkan proses dari pembicara sebelumnya. Gunakan satu pesanan demo yang sama sejak dibuat hingga diselesaikan agar video terasa sebagai satu cerita.")

table = doc.add_table(rows=1, cols=4)
table.alignment = WD_TABLE_ALIGNMENT.CENTER
table.autofit = False
widths = [Cm(2.5), Cm(4.0), Cm(7.2), Cm(2.4)]
headers = ["Anggota", "Bagian", "Fitur utama", "Durasi"]
for i, (cell, text) in enumerate(zip(table.rows[0].cells, headers)):
    cell.width = widths[i]
    cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
    set_cell_shading(cell, BLUE)
    set_cell_border(cell)
    set_cell_margins(cell)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    font_run(p.add_run(text), size=9.5, bold=True, color=RGBColor(255, 255, 255))

rows = [
    ("Kevin", "Pembukaan dan akses", "Latar belakang, login, pengguna, role, dan permission", "3 menit"),
    ("Kiki", "Master data", "Produk, layanan, tarif, channel, dan metode pembayaran", "3–4 menit"),
    ("Jonedi", "Pembuatan pesanan", "Pelanggan, item produk atau jasa, draft, dan konfirmasi", "4 menit"),
    ("Gandewa", "Proses transaksi", "Status, pembayaran, penyelesaian, pembatalan, dan stok", "4 menit"),
    ("Jericho", "Monitoring dan penutup", "Dashboard, laporan penjualan, audit log, dan kesimpulan", "3–4 menit"),
]
for ri, row_data in enumerate(rows):
    cells = table.add_row().cells
    for ci, (cell, value) in enumerate(zip(cells, row_data)):
        cell.width = widths[ci]
        cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
        set_cell_shading(cell, PALE_BLUE if ri % 2 else "FFFFFF")
        set_cell_border(cell)
        set_cell_margins(cell)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER if ci in (0, 3) else WD_ALIGN_PARAGRAPH.LEFT
        font_run(p.add_run(value), size=9.3, bold=(ci == 0))

doc.add_heading("Alur Utama", level=2)
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.paragraph_format.space_before = Pt(4)
p.paragraph_format.space_after = Pt(8)
font_run(p.add_run("Kevin  →  Kiki  →  Jonedi  →  Gandewa  →  Jericho"), size=12, bold=True, color=RGBColor(23, 74, 126))
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
font_run(p.add_run("Akses sistem  →  Data dasar  →  Pesanan  →  Pembayaran dan stok  →  Laporan"), size=10)

doc.add_page_break()

add_member_section(
    doc, 1, "Kevin", "Pembukaan, login, dan hak akses", "3 menit",
    "Memberikan konteks aplikasi dan menunjukkan bahwa akses fitur dikendalikan berdasarkan peran pengguna.",
    [
        "Perkenalkan tim dan tujuan presentasi.",
        "Jelaskan masalah operasional yang ingin diselesaikan oleh Edira Fotocopy.",
        "Buka halaman login lalu masuk sebagai administrator.",
        "Tampilkan menu manajemen pengguna dan role.",
        "Jelaskan perbedaan hak akses administrator dan pegawai.",
    ],
    [
        "Halo, kami akan mempresentasikan Edira Fotocopy, yaitu sistem yang dikembangkan untuk membantu pengelolaan master data, transaksi pesanan, pembayaran, persediaan, dan laporan penjualan pada usaha fotokopi.",
        "Sistem dilengkapi autentikasi dan pembagian hak akses. Administrator dapat mengelola pengguna, role, master data, transaksi, stok, laporan, dan audit log, sedangkan pegawai berfokus pada kegiatan operasional transaksi.",
    ],
    "Sebelum transaksi dibuat, sistem memerlukan data produk dan layanan. Bagian master data akan dijelaskan oleh Kiki.",
)

doc.add_page_break()

add_member_section(
    doc, 2, "Kiki", "Pengelolaan master data", "3 sampai 4 menit",
    "Menjelaskan data dasar yang digunakan sistem untuk membentuk pilihan produk, layanan, harga, channel, dan pembayaran.",
    [
        "Tampilkan kategori dan satuan sebagai dasar data produk.",
        "Buka data produk dan jelaskan harga jual, stok, serta batas minimum stok.",
        "Tampilkan jenis kertas, jenis layanan, dan mode cetak.",
        "Buka tarif layanan dan jelaskan kombinasi layanan, kertas, mode cetak, harga, dan masa berlaku.",
        "Tampilkan channel pesanan dan metode pembayaran.",
        "Lakukan satu contoh tambah atau ubah produk dan satu contoh tarif layanan. Tidak perlu melakukan CRUD pada seluruh menu.",
    ],
    [
        "Master data menjadi dasar dalam proses transaksi. Produk dikelompokkan berdasarkan kategori dan satuan, serta memiliki harga jual, stok, dan batas minimum stok.",
        "Tarif layanan dapat dibedakan berdasarkan jenis layanan, jenis kertas, mode cetak, dan masa berlaku harga. Hanya data yang aktif dan masih berlaku yang tersedia pada form pesanan.",
    ],
    "Setelah data produk dan layanan tersedia, proses berikutnya adalah membuat pesanan pelanggan. Alur tersebut akan dijelaskan oleh Jonedi.",
)

doc.add_page_break()

add_member_section(
    doc, 3, "Jonedi", "Pelanggan dan pembuatan pesanan", "4 menit",
    "Mendemonstrasikan proses inti ketika pegawai menerima pesanan yang berisi produk, layanan fotokopi, atau keduanya.",
    [
        "Buka daftar pesanan lalu pilih Buat Pesanan.",
        "Pilih channel pesanan.",
        "Pilih pelanggan tersimpan atau masukkan pelanggan baru.",
        "Tambahkan satu item produk.",
        "Tambahkan satu layanan fotokopi dan masukkan jumlah halaman serta jumlah copy.",
        "Tunjukkan estimasi jumlah lembar dan subtotal.",
        "Simpan pesanan sebagai draft, buka detailnya, lalu ubah status menjadi dikonfirmasi.",
    ],
    [
        "Pesanan dapat berisi produk maupun layanan dalam satu transaksi. Untuk layanan fotokopi, pengguna memasukkan jumlah halaman dan jumlah salinan, kemudian sistem menghitung jumlah lembar serta estimasi subtotal.",
        "Perhitungan final dilakukan kembali pada server. Nama item dan harga disimpan sebagai snapshot agar transaksi lama tidak berubah ketika harga pada master data diperbarui.",
    ],
    "Pesanan yang sudah dikonfirmasi selanjutnya masuk ke proses pengerjaan, pembayaran, dan penyelesaian. Bagian tersebut akan dijelaskan oleh Gandewa.",
)

doc.add_heading("Data Demo yang Disarankan", level=2)
demo_table = doc.add_table(rows=1, cols=2)
demo_table.alignment = WD_TABLE_ALIGNMENT.CENTER
demo_table.autofit = False
demo_widths = [Cm(4.4), Cm(11.7)]
for i, text in enumerate(("Data", "Contoh nilai")):
    cell = demo_table.rows[0].cells[i]
    cell.width = demo_widths[i]
    set_cell_shading(cell, BLUE)
    set_cell_border(cell)
    set_cell_margins(cell)
    cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
    font_run(cell.paragraphs[0].add_run(text), size=9.5, bold=True, color=RGBColor(255, 255, 255))
for ri, values in enumerate([
    ("Pelanggan", "Budi Santoso"),
    ("Channel", "Datang langsung"),
    ("Produk", "Pulpen atau produk lain yang stoknya mencukupi"),
    ("Layanan", "Fotokopi A4 hitam putih"),
    ("Jumlah", "10 halaman dan 2 copy"),
]):
    cells = demo_table.add_row().cells
    for ci, value in enumerate(values):
        cells[ci].width = demo_widths[ci]
        cells[ci].vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
        set_cell_shading(cells[ci], PALE_BLUE if ri % 2 else "FFFFFF")
        set_cell_border(cells[ci])
        set_cell_margins(cells[ci])
        font_run(cells[ci].paragraphs[0].add_run(value), size=9.3, bold=(ci == 0))

doc.add_page_break()

add_member_section(
    doc, 4, "Gandewa", "Status pesanan, pembayaran, dan stok", "4 menit",
    "Melanjutkan pesanan yang dibuat Jonedi sampai lunas dan selesai, kemudian menunjukkan dampaknya terhadap persediaan.",
    [
        "Buka detail pesanan yang dibuat pada bagian sebelumnya.",
        "Ubah status dari dikonfirmasi menjadi diproses.",
        "Catat pembayaran sebagian atau langsung lunas.",
        "Jika menggunakan pembayaran sebagian, tunjukkan sisa tagihan dan catat pelunasannya.",
        "Ubah status pesanan menjadi selesai.",
        "Buka pergerakan stok dan tunjukkan pengurangan stok akibat transaksi.",
        "Jelaskan alur pembatalan dan pencatatan bahan terpakai tanpa membatalkan pesanan demo utama.",
    ],
    [
        "Sistem mendukung pembayaran bertahap. Setiap pembayaran mencatat metode, nominal, penerima, waktu, serta nomor referensi apabila tersedia.",
        "Pesanan hanya dapat diselesaikan setelah tagihan lunas. Ketika status menjadi selesai, sistem otomatis mencatat pengurangan stok produk dan pemakaian bahan untuk layanan.",
        "Jika pesanan dibatalkan setelah sebagian bahan digunakan, jumlah bahan tersebut dapat dicatat sebagai waste agar data persediaan tetap akurat.",
    ],
    "Seluruh aktivitas transaksi kemudian dirangkum dalam dashboard, laporan penjualan, dan audit log yang akan dijelaskan oleh Jericho.",
)

p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.paragraph_format.space_before = Pt(5)
font_run(p.add_run("Draft  →  Dikonfirmasi  →  Diproses  →  Selesai"), size=12, bold=True, color=RGBColor(23, 74, 126))
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
font_run(p.add_run("Pada tahap yang diizinkan, pesanan juga dapat dibatalkan dengan alasan yang jelas."), size=9.5, color=RGBColor(75, 85, 99))

doc.add_page_break()

add_member_section(
    doc, 5, "Jericho", "Dashboard, laporan, audit log, dan penutup", "3 sampai 4 menit",
    "Menunjukkan bagaimana data transaksi diringkas untuk pemantauan usaha dan menutup presentasi dengan kesimpulan pengembangan.",
    [
        "Buka dashboard dan jelaskan kartu statistik operasional.",
        "Jelaskan tren penjualan, distribusi status pesanan, dan komposisi produk serta layanan.",
        "Tunjukkan daftar pesanan terbaru dan informasi stok menipis.",
        "Buka laporan penjualan lalu gunakan filter tanggal.",
        "Jelaskan total penjualan, jumlah transaksi, rata-rata transaksi, dan metode pembayaran.",
        "Buka audit log dan cari aktivitas yang baru dilakukan selama demo.",
        "Sampaikan kesimpulan dan rencana pengembangan berikutnya.",
    ],
    [
        "Dashboard memberikan ringkasan operasional seperti jumlah pesanan hari ini, pesanan yang sedang diproses, transaksi selesai, penjualan, dan produk dengan stok menipis.",
        "Audit log mencatat pengguna, aktivitas, waktu, perubahan data, alamat IP, dan objek yang diubah sehingga administrator dapat menelusuri aktivitas penting dalam sistem.",
        "Edira Fotocopy telah mencakup alur utama mulai dari pengelolaan data, pembuatan pesanan, pembayaran, kontrol stok, hingga laporan. Pengembangan berikutnya dapat mencakup pencetakan nota, notifikasi pelanggan, integrasi pembayaran digital, dan penyempurnaan laporan.",
    ],
    "Demikian presentasi sistem Edira Fotocopy dari tim kami. Terima kasih.",
)

doc.add_page_break()

doc.add_heading("Checklist Sebelum Rekaman", level=1)
for item in [
    "Pastikan tersedia satu akun administrator dan satu akun pegawai.",
    "Siapkan kategori, satuan, produk aktif, dan stok yang mencukupi.",
    "Siapkan satu produk dengan stok mendekati batas minimum agar muncul di dashboard.",
    "Pastikan jenis kertas, layanan, mode cetak, dan tarif aktif sudah tersedia.",
    "Siapkan minimal satu channel pesanan dan dua metode pembayaran.",
    "Tambahkan beberapa transaksi selesai pada tanggal berbeda agar grafik dan laporan terisi.",
    "Gunakan satu transaksi yang sama dari bagian Jonedi hingga Gandewa.",
    "Samakan resolusi layar, zoom browser, volume mikrofon, dan posisi kursor.",
    "Tutup tab atau aplikasi yang tidak berkaitan sebelum merekam.",
    "Lakukan satu kali latihan penuh dan catat durasi setiap pembicara.",
]:
    add_bullet(doc, "☐ " + item)

doc.add_heading("Keamanan Saat Merekam", level=2)
for item in [
    "Jangan menampilkan file .env, APP_KEY, password, token, atau kredensial database.",
    "Gunakan data pelanggan fiktif dan alamat yang tidak mengarah kepada orang nyata.",
    "Pastikan notifikasi pribadi, email, dan aplikasi komunikasi tidak terlihat.",
    "Sembunyikan informasi deployment yang bersifat rahasia.",
]:
    add_bullet(doc, item)

doc.add_heading("Saran Penyuntingan Video", level=2)
for item in [
    "Tampilkan nama anggota dan judul bagiannya selama 2 sampai 3 detik pada setiap pergantian pembicara.",
    "Potong waktu tunggu halaman, kesalahan klik, dan jeda yang terlalu panjang.",
    "Gunakan musik latar sangat pelan atau tanpa musik agar narasi tetap jelas.",
    "Tambahkan teks singkat untuk menandai tahap penting, tetapi hindari memenuhi layar dengan tulisan.",
    "Akhiri video dengan identitas tim dan ucapan terima kasih.",
]:
    add_bullet(doc, item)

# Footer with page field
footer = section.footer
p = footer.paragraphs[0]
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = p.add_run("Edira Fotocopy  •  Panduan Presentasi  •  ")
font_run(r, size=8.5, color=RGBColor(107, 114, 128))
fld_char1 = OxmlElement("w:fldChar")
fld_char1.set(qn("w:fldCharType"), "begin")
instr = OxmlElement("w:instrText")
instr.set(qn("xml:space"), "preserve")
instr.text = " PAGE "
fld_char2 = OxmlElement("w:fldChar")
fld_char2.set(qn("w:fldCharType"), "end")
r._r.append(fld_char1)
r._r.append(instr)
r._r.append(fld_char2)

OUTPUT.parent.mkdir(parents=True, exist_ok=True)
doc.save(OUTPUT)
print(OUTPUT)
