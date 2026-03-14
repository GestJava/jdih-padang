#!/usr/bin/env python3
"""
QR Code Generator untuk JDIH System
Menggunakan Python qrcode library sebagai alternatif Google Charts API
Mendukung logo di tengah QR code
"""

import sys
import os
import qrcode
from PIL import Image

def generate_qr_code(data, output_path, size=100, logo_path=None):
    """
    Generate QR code menggunakan Python qrcode library dengan optional logo
    
    Args:
        data (str): Data yang akan di-encode ke QR code
        output_path (str): Path file output
        size (int): Ukuran QR code dalam pixel
        logo_path (str, optional): Path ke file logo untuk ditempatkan di tengah QR code
    
    Returns:
        bool: True jika berhasil, False jika gagal
    """
    try:
        print(f"Generating QR code for: {data}")
        print(f"Output path: {output_path}")
        print(f"Size: {size}x{size}")
        if logo_path:
            print(f"Logo path: {logo_path}")
        
        # Pastikan direktori output ada
        output_dir = os.path.dirname(output_path)
        if not os.path.exists(output_dir):
            os.makedirs(output_dir, exist_ok=True)
            print(f"Created directory: {output_dir}")
        
        # Buat QR code dengan error correction HIGH untuk support logo
        error_correction = qrcode.constants.ERROR_CORRECT_H if logo_path else qrcode.constants.ERROR_CORRECT_L
        
        qr = qrcode.QRCode(
            version=1,
            error_correction=error_correction,
            box_size=10,
            border=4,
        )
        
        qr.add_data(data)
        qr.make(fit=True)
        
        # Buat image
        img = qr.make_image(fill_color="black", back_color="white").convert('RGB')
        
        # Tambahkan logo jika ada
        if logo_path:
            print(f"Checking logo path: {logo_path}")
            print(f"Logo file exists: {os.path.exists(logo_path)}")
            if os.path.exists(logo_path):
                try:
                    print(f"Attempting to open logo: {logo_path}")
                    # Buka logo (PIL/Pillow mendukung berbagai format: PNG, ICO, JPG, dll)
                    logo = Image.open(logo_path)
                    print(f"Logo opened successfully. Format: {logo.format}, Mode: {logo.mode}, Size: {logo.size}")
                    
                    # Resize logo menjadi 25% dari QR code size (standar aman agar QR tetap bisa di-scan)
                    logo_size = int(size * 0.25)
                    print(f"Resizing logo to {logo_size}x{logo_size}px")
                    logo = logo.resize((logo_size, logo_size), Image.Resampling.LANCZOS)
                    
                    # Convert logo ke RGBA jika belum (untuk support transparansi)
                    if logo.mode != 'RGBA':
                        print(f"Converting logo from {logo.mode} to RGBA")
                        # Jika ICO atau format lain tanpa alpha, tambahkan alpha channel
                        if logo.mode in ('RGB', 'L', 'P'):
                            logo = logo.convert('RGBA')
                        else:
                            logo = logo.convert('RGBA')
                    
                    # Posisi logo di tengah QR code
                    img_width, img_height = img.size
                    logo_width, logo_height = logo.size
                    
                    # Hitung posisi tengah
                    position = ((img_width - logo_width) // 2, (img_height - logo_height) // 2)
                    print(f"Pasting logo at position: {position}")
                    
                    # Paste logo ke QR code dengan alpha blending
                    img.paste(logo, position, logo)
                    
                    print(f"Logo added successfully: {logo_path} (size: {logo_size}x{logo_size}px)")
                except Exception as e:
                    print(f"ERROR: Failed to add logo: {str(e)}")
                    import traceback
                    print(f"Traceback: {traceback.format_exc()}")
                    # Continue tanpa logo jika gagal
            else:
                print(f"WARNING: Logo file not found at: {logo_path}")
        else:
            print("No logo path provided - generating QR code without logo")
        
        # Resize jika diperlukan
        if size != 100:
            img = img.resize((size, size), Image.Resampling.LANCZOS)
        
        # Simpan image
        img.save(output_path, "PNG")
        
        # Verifikasi file tersimpan
        if os.path.exists(output_path):
            file_size = os.path.getsize(output_path)
            print(f"QR code generated successfully: {output_path}")
            print(f"File size: {file_size} bytes")
            return True
        else:
            print(f"Error: Output file not created: {output_path}")
            return False
            
    except Exception as e:
        print(f"Error generating QR code: {str(e)}")
        import traceback
        print(traceback.format_exc())
        return False

def main():
    """Main function untuk command line usage"""
    if len(sys.argv) < 3:
        print("Usage: python generate_qr_code.py <data> <output_path> [size] [logo_path]")
        print("Example: python generate_qr_code.py 'https://example.com' 'qr.png' 100")
        print("Example with logo: python generate_qr_code.py 'https://example.com' 'qr.png' 100 '/path/to/logo.png'")
        return False
    
    data = sys.argv[1]
    output_path = sys.argv[2]
    size = int(sys.argv[3]) if len(sys.argv) > 3 else 100
    logo_path = sys.argv[4] if len(sys.argv) > 4 else None
    
    return generate_qr_code(data, output_path, size, logo_path)

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)