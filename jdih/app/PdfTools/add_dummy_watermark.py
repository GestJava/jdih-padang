import sys
import fitz

if len(sys.argv) < 3:
    print("Usage: python add_dummy_watermark.py input.pdf output.pdf")
    sys.exit(1)

input_pdf = sys.argv[1]
output_pdf = sys.argv[2]

watermark_text = "TANDA TANGAN ELEKTRONIK (SIMULASI)"

# Open the PDF
pdf = fitz.open(input_pdf)

for page in pdf:
    rect = page.rect
    # Center position
    x = rect.width / 2
    y = rect.height / 2
    # Add watermark text (large, gray, rotated)
    page.insert_text(
        (x, y),
        watermark_text,
        fontsize=36,
        rotate=0,
        color=(0.7, 0.7, 0.7),
        fontname="helv",
        render_mode=3  # Fill text with stroke
    )

pdf.save(output_pdf)
print(f"Watermark berhasil ditambahkan ke {output_pdf}")
