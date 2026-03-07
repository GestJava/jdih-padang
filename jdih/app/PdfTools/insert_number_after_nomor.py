import os
import sys
import fitz

def main():
    try:
        # Validasi argumen
        if len(sys.argv) < 4:
            print("Usage: python insert_number_after_nomor.py input.pdf output.pdf number")
            sys.exit(1)

        input_pdf = sys.argv[1]
        output_pdf = sys.argv[2]
        number = sys.argv[3]

        # Validasi file input
        if not os.path.exists(input_pdf):
            print(f"Error: File input tidak ditemukan: {input_pdf}")
            sys.exit(1)

        # Dapatkan direktori skrip saat ini
        script_dir = os.path.dirname(os.path.abspath(__file__))
        font_path = os.path.join(script_dir, 'fonts', 'BOOKOS.TTF')
        
        # Validasi font
        if not os.path.exists(font_path):
            print(f"Error: File font tidak ditemukan: {font_path}")
            sys.exit(1)

        # Buka dokumen PDF
        try:
            doc = fitz.open(input_pdf)
        except Exception as e:
            print(f"Error: Gagal membuka file PDF: {str(e)}")
            sys.exit(1)

        found = False
        text_to_find = "NOMOR"
        
        # Cari teks "NOMOR" di setiap halaman
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            text_instances = page.search_for(text_to_find)
            
            if text_instances:
                # Ambil instance pertama yang ditemukan
                rect = text_instances[0]
                
                # Hitung posisi untuk menempatkan nomor
                x = rect.x1 + 15  # Offset dari teks "NOMOR"
                y = rect.y0 + 5   # Offset vertikal
                
                # Tambahkan kotak latar belakang putih untuk menutupi teks lama
                bg_rect = fitz.Rect(
                    x - 2, y - 12,
                    x + (len(str(number)) * 8), y + 5
                )
                page.draw_rect(bg_rect, color=(1, 1, 1), fill=(1, 1, 1), overlay=False)
                
                # Sisipkan nomor baru
                page.insert_text(
                    (x, y),
                    str(number),
                    fontsize=12,
                    fontfile=font_path,
                    color=(0, 0, 0),  # Warna hitam
                    overlay=True
                )
                
                found = True
                print(f"Nomor berhasil disisipkan di halaman {page_num + 1}")
                break  # Hanya sisipkan di kemunculan pertama

        # Simpan dokumen
        if found:
            # Pastikan direktori output ada
            os.makedirs(os.path.dirname(output_pdf), exist_ok=True)
            doc.save(output_pdf)
            print(f"Berhasil menyimpan PDF ke: {output_pdf}")
            sys.exit(0)
        else:
            print(f"Peringatan: Teks '{text_to_find}' tidak ditemukan dalam dokumen")
            sys.exit(1)

    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)
    finally:
        if 'doc' in locals():
            doc.close()

if __name__ == "__main__":
    main()