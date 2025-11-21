"""
Aplikasi Generator Sertifikat Pendidik - FINAL VERSION
STK Santo Yakobus Merauke

PERUBAHAN v1.3:
- PDF UKMPPG langsung digunakan sebagai konten (tidak di-extract ulang)
- Hanya menambahkan: Nomor Seri, TTD, dan QR Code
- Koordinat FINE-TUNED berdasarkan pengukuran manual + screenshot actual output

Author: Claude AI Assistant
Version: 1.3 (FINAL - FINE-TUNED)
"""

from flask import Flask, render_template, request, send_file, jsonify, send_from_directory
from werkzeug.utils import secure_filename
import os
import json
from pathlib import Path
import zipfile
from datetime import datetime
import shutil

# Import PDF processing modules
from pypdf import PdfReader, PdfWriter
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import A4
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.utils import ImageReader
from PIL import Image
import io

app = Flask(__name__)
app.config['SECRET_KEY'] = 'stkYakobus2024'
app.config['MAX_CONTENT_LENGTH'] = 500 * 1024 * 1024  # 500MB max
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['OUTPUT_FOLDER'] = 'output'

# Allowed extensions
ALLOWED_EXTENSIONS = {'pdf', 'png', 'jpg', 'jpeg'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def extract_text_from_pdf(pdf_path):
    """Extract text from PDF UKMPPG - HANYA untuk penamaan file"""
    try:
        reader = PdfReader(pdf_path)
        text = ""
        for page in reader.pages:
            text += page.extract_text()
        return text
    except Exception as e:
        print(f"Error extracting text: {e}")
        return ""

def parse_certificate_data(text):
    """Parse data mahasiswa dari text PDF - HANYA untuk penamaan file"""
    data = {
        'nama': '',
        'nim': ''
    }
    
    try:
        lines = text.split('\n')
        for i, line in enumerate(lines):
            line = line.strip()
            
            # Extract Nama (biasanya dalam huruf kapital semua)
            if line.isupper() and len(line) > 10 and 'KEMENTERIAN' not in line and 'SEKOLAH' not in line and 'SERTIFIKAT' not in line:
                if not data['nama']:
                    data['nama'] = line.strip()
            
            # Extract NIM
            if 'Nomor Induk Mahasiswa:' in line or 'Nomor Induk Mahasiswa :' in line:
                nim_part = line.replace('Nomor Induk Mahasiswa:', '').replace('Nomor Induk Mahasiswa :', '').strip()
                if nim_part:
                    data['nim'] = nim_part
                
    except Exception as e:
        print(f"Error parsing data: {e}")
    
    # Fallback jika tidak ketemu
    if not data['nama']:
        data['nama'] = 'UNKNOWN'
    if not data['nim']:
        data['nim'] = '00000000000'
    
    return data

def create_overlay_pdf(nomor_seri, ttd_ketua_path, ttd_kaprodi_path, 
                       qr_ketua_path, qr_kaprodi_path):
    """Create overlay PDF with ONLY nomor seri, TTD, and QR codes - FINE-TUNED"""
    
    # Create a BytesIO buffer
    packet = io.BytesIO()
    
    # Create canvas with A4 landscape (like certificate)
    # 842 x 595 points = A4 landscape
    can = canvas.Canvas(packet, pagesize=(842, 595))
    
    # ========== KOORDINAT FINE-TUNED v1.3 ==========
    # Berdasarkan pengukuran manual + adjustment dari screenshot actual output
    
    # NOMOR SERI - Posisi SUDAH BAGUS dari screenshot ✅
    can.setFont("Helvetica", 11)
    can.drawString(746, 553, nomor_seri)
    
    # TTD KETUA - Diperkecil 30% dan geser sedikit ke kanan
    # Screenshot menunjukkan TTD terlalu besar, jadi dikecilkan
    if os.path.exists(ttd_ketua_path):
        try:
            img = ImageReader(ttd_ketua_path)
            can.drawImage(img, 130, 105, width=70, height=60, mask='auto', preserveAspectRatio=True)
        except Exception as e:
            print(f"Error adding TTD Ketua: {e}")
    
    # QR CODE KETUA - Geser lebih ke kanan untuk spacing dari TTD
    if os.path.exists(qr_ketua_path):
        try:
            img = ImageReader(qr_ketua_path)
            can.drawImage(img, 240, 105, width=60, height=60, mask='auto')
        except Exception as e:
            print(f"Error adding QR Ketua: {e}")
    
    # TTD KAPRODI - SUDAH BAGUS dari screenshot ✅
    if os.path.exists(ttd_kaprodi_path):
        try:
            img = ImageReader(ttd_kaprodi_path)
            can.drawImage(img, 590, 105, width=113, height=48, mask='auto', preserveAspectRatio=True)
        except Exception as e:
            print(f"Error adding TTD Kaprodi: {e}")
    
    # QR CODE KAPRODI - SUDAH BAGUS dari screenshot ✅
    if os.path.exists(qr_kaprodi_path):
        try:
            img = ImageReader(qr_kaprodi_path)
            can.drawImage(img, 723, 105, width=60, height=60, mask='auto')
        except Exception as e:
            print(f"Error adding QR Kaprodi: {e}")
    
    can.save()
    
    # Move to the beginning of the BytesIO buffer
    packet.seek(0)
    return packet

def generate_certificate(blanko_path, pdf_ukmppg_path, nomor_seri,
                         ttd_ketua_path, ttd_kaprodi_path,
                         qr_ketua_path, qr_kaprodi_path,
                         output_path):
    """Generate final certificate PDF by merging: Blanko + PDF UKMPPG + Overlay"""
    
    try:
        # Extract nama dan NIM HANYA untuk penamaan file
        text = extract_text_from_pdf(pdf_ukmppg_path)
        data = parse_certificate_data(text)
        
        print(f"Processing: {data['nama']} (NIM: {data['nim']})")
        
        # Read PDF UKMPPG (ini yang punya semua data mahasiswa lengkap)
        ukmppg_reader = PdfReader(pdf_ukmppg_path)
        
        # Read blanko as background
        blanko_reader = PdfReader(blanko_path)
        
        # Create overlay dengan hanya nomor seri, TTD, dan QR
        overlay_packet = create_overlay_pdf(
            nomor_seri,
            ttd_ketua_path, ttd_kaprodi_path,
            qr_ketua_path, qr_kaprodi_path
        )
        overlay_reader = PdfReader(overlay_packet)
        
        # Create output
        output = PdfWriter()
        
        # ========== MERGE STRATEGY ==========
        # Layer 1: Blanko (background)
        # Layer 2: PDF UKMPPG (content dengan data mahasiswa lengkap)
        # Layer 3: Overlay (nomor seri + TTD + QR)
        
        # Start with blanko as base
        page = blanko_reader.pages[0]
        
        # Merge UKMPPG content (ini yang punya data mahasiswa lengkap)
        if len(ukmppg_reader.pages) > 0:
            page.merge_page(ukmppg_reader.pages[0])
        
        # Merge overlay (nomor seri + TTD + QR)
        page.merge_page(overlay_reader.pages[0])
        
        output.add_page(page)
        
        # Write output
        with open(output_path, 'wb') as output_file:
            output.write(output_file)
        
        print(f"✅ Generated: {output_path}")
        return True, data['nama'], data['nim']
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        print(f"❌ Error generating certificate: {e}")
        return False, str(e), ""

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/upload', methods=['POST'])
def upload_files():
    """Handle file uploads"""
    try:
        # Try to use configured upload folder, fallback to temp if permission error
        upload_folder = app.config['UPLOAD_FOLDER']
        
        try:
            # Clear previous uploads
            if os.path.exists(upload_folder):
                shutil.rmtree(upload_folder)
            os.makedirs(upload_folder, exist_ok=True)
        except (PermissionError, OSError) as e:
            # Fallback to temp directory
            import tempfile
            upload_folder = tempfile.mkdtemp(prefix='sertifikat_')
            print(f"⚠️ Using temporary directory: {upload_folder}")
        
        # Get uploaded files
        blanko = request.files.get('blanko')
        pdf_files = request.files.getlist('pdf_files')
        ttd_ketua = request.files.get('ttd_ketua')
        ttd_kaprodi = request.files.get('ttd_kaprodi')
        qr_ketua = request.files.get('qr_ketua')
        qr_kaprodi = request.files.get('qr_kaprodi')
        
        # Get nomor seri range
        nomor_awal = request.form.get('nomor_awal')
        
        # Validate
        if not all([blanko, pdf_files, ttd_ketua, ttd_kaprodi, qr_ketua, qr_kaprodi, nomor_awal]):
            return jsonify({'error': 'Semua file harus diupload'}), 400
        
        # Save blanko
        blanko_filename = secure_filename(blanko.filename)
        blanko_path = os.path.join(upload_folder, 'blanko.pdf')
        blanko.save(blanko_path)
        
        # Save PDF files
        pdf_folder = os.path.join(upload_folder, 'pdfs')
        os.makedirs(pdf_folder, exist_ok=True)
        
        pdf_paths = []
        for pdf_file in pdf_files:
            if pdf_file and allowed_file(pdf_file.filename):
                filename = secure_filename(pdf_file.filename)
                filepath = os.path.join(pdf_folder, filename)
                pdf_file.save(filepath)
                pdf_paths.append(filepath)
        
        # Save signatures
        ttd_ketua_path = os.path.join(upload_folder, 'ttd_ketua.png')
        ttd_ketua.save(ttd_ketua_path)
        
        ttd_kaprodi_path = os.path.join(upload_folder, 'ttd_kaprodi.png')
        ttd_kaprodi.save(ttd_kaprodi_path)
        
        # Save QR codes
        qr_ketua_path = os.path.join(upload_folder, 'qr_ketua.png')
        qr_ketua.save(qr_ketua_path)
        
        qr_kaprodi_path = os.path.join(upload_folder, 'qr_kaprodi.png')
        qr_kaprodi.save(qr_kaprodi_path)
        
        # Store session data
        session_data = {
            'blanko_path': blanko_path,
            'pdf_paths': pdf_paths,
            'ttd_ketua_path': ttd_ketua_path,
            'ttd_kaprodi_path': ttd_kaprodi_path,
            'qr_ketua_path': qr_ketua_path,
            'qr_kaprodi_path': qr_kaprodi_path,
            'nomor_awal': int(nomor_awal),
            'total_files': len(pdf_paths),
            'upload_folder': upload_folder  # Store the actual folder used
        }
        
        # Save to JSON
        session_file = os.path.join(upload_folder, 'session.json')
        with open(session_file, 'w') as f:
            json.dump(session_data, f)
        
        return jsonify({
            'success': True,
            'message': f'{len(pdf_paths)} file PDF berhasil diupload',
            'total_files': len(pdf_paths)
        })
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({'error': f'Upload error: {str(e)}'}), 500

@app.route('/generate', methods=['POST'])
def generate():
    """Generate all certificates"""
    try:
        # Load session data - find session.json in possible locations
        session_file = None
        for possible_folder in [app.config['UPLOAD_FOLDER'], 
                               os.path.join(os.getcwd(), 'uploads')]:
            test_path = os.path.join(possible_folder, 'session.json')
            if os.path.exists(test_path):
                session_file = test_path
                break
        
        if not session_file:
            # Try to find session.json in temp directories
            import tempfile
            temp_dir = tempfile.gettempdir()
            for item in os.listdir(temp_dir):
                if item.startswith('sertifikat_'):
                    test_path = os.path.join(temp_dir, item, 'session.json')
                    if os.path.exists(test_path):
                        session_file = test_path
                        break
        
        if not session_file:
            return jsonify({'error': 'Session data tidak ditemukan. Silakan upload ulang file.'}), 400
        
        with open(session_file, 'r') as f:
            session_data = json.load(f)
        
        # Get upload folder from session or use config
        upload_folder = session_data.get('upload_folder', app.config['UPLOAD_FOLDER'])
        
        # Clear output folder or use temp
        output_folder = app.config['OUTPUT_FOLDER']
        try:
            if os.path.exists(output_folder):
                shutil.rmtree(output_folder)
            os.makedirs(output_folder, exist_ok=True)
        except (PermissionError, OSError):
            # Fallback to temp directory
            import tempfile
            output_folder = tempfile.mkdtemp(prefix='sertifikat_output_')
            print(f"⚠️ Using temporary output directory: {output_folder}")
        
        results = []
        nomor_seri = session_data['nomor_awal']
        
        print("\n" + "="*60)
        print("🎓 MULAI GENERATE SERTIFIKAT")
        print("="*60)
        
        # Generate each certificate
        for i, pdf_path in enumerate(session_data['pdf_paths']):
            nomor_seri_str = str(nomor_seri).zfill(7)
            
            print(f"\n[{i+1}/{len(session_data['pdf_paths'])}] Processing: {os.path.basename(pdf_path)}")
            print(f"   Nomor Seri: {nomor_seri_str}")
            
            # Extract nama for filename
            text = extract_text_from_pdf(pdf_path)
            data = parse_certificate_data(text)
            
            output_filename = f"Sertifikat_{data['nim']}_{data['nama'].replace(' ', '_')}.pdf"
            output_path = os.path.join(output_folder, output_filename)
            
            success, nama, nim = generate_certificate(
                session_data['blanko_path'],
                pdf_path,
                nomor_seri_str,
                session_data['ttd_ketua_path'],
                session_data['ttd_kaprodi_path'],
                session_data['qr_ketua_path'],
                session_data['qr_kaprodi_path'],
                output_path
            )
            
            if success:
                results.append({
                    'status': 'success',
                    'nama': nama,
                    'nim': nim,
                    'nomor_seri': nomor_seri_str,
                    'filename': output_filename
                })
            else:
                results.append({
                    'status': 'error',
                    'error': nama,
                    'filename': os.path.basename(pdf_path)
                })
            
            nomor_seri += 1
        
        print("\n" + "="*60)
        print(f"✅ SELESAI! Generated {len(results)} sertifikat")
        print("="*60 + "\n")
        
        # Store output folder for download
        session_data['output_folder'] = output_folder
        with open(session_file, 'w') as f:
            json.dump(session_data, f)
        
        return jsonify({
            'success': True,
            'results': results,
            'total': len(results),
            'output_folder': output_folder
        })
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({'error': f'Generate error: {str(e)}'}), 500

@app.route('/download/<filename>')
def download_file(filename):
    """Download individual certificate"""
    try:
        # Try to find the file in output folder or session data
        output_folder = app.config['OUTPUT_FOLDER']
        file_path = os.path.join(output_folder, filename)
        
        if not os.path.exists(file_path):
            # Try to get from session data
            for possible_folder in [app.config['UPLOAD_FOLDER'], 
                                   os.path.join(os.getcwd(), 'uploads')]:
                session_file = os.path.join(possible_folder, 'session.json')
                if os.path.exists(session_file):
                    with open(session_file, 'r') as f:
                        session_data = json.load(f)
                    output_folder = session_data.get('output_folder', output_folder)
                    file_path = os.path.join(output_folder, filename)
                    if os.path.exists(file_path):
                        break
        
        if os.path.exists(file_path):
            return send_file(file_path, as_attachment=True, download_name=filename)
        else:
            return jsonify({'error': 'File tidak ditemukan'}), 404
            
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/download-all')
def download_all():
    """Download all certificates as ZIP"""
    try:
        # Find output folder from session or use default
        output_folder = app.config['OUTPUT_FOLDER']
        
        # Try to get from session data
        for possible_folder in [app.config['UPLOAD_FOLDER'], 
                               os.path.join(os.getcwd(), 'uploads')]:
            session_file = os.path.join(possible_folder, 'session.json')
            if os.path.exists(session_file):
                with open(session_file, 'r') as f:
                    session_data = json.load(f)
                output_folder = session_data.get('output_folder', output_folder)
                break
        
        # Also check temp directories
        if not os.path.exists(output_folder) or not os.listdir(output_folder):
            import tempfile
            temp_dir = tempfile.gettempdir()
            for item in os.listdir(temp_dir):
                if item.startswith('sertifikat_output_'):
                    test_folder = os.path.join(temp_dir, item)
                    if os.path.exists(test_folder) and os.listdir(test_folder):
                        output_folder = test_folder
                        break
        
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        zip_filename = f'Sertifikat_Batch_{timestamp}.zip'
        
        # Create ZIP in temp directory to avoid permission issues
        import tempfile
        zip_path = os.path.join(tempfile.gettempdir(), zip_filename)
        
        # Create ZIP file
        with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for root, dirs, files in os.walk(output_folder):
                for file in files:
                    if file.endswith('.pdf'):
                        file_path = os.path.join(root, file)
                        zipf.write(file_path, file)
        
        return send_file(zip_path, as_attachment=True, download_name=zip_filename)
        
    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({'error': f'Download error: {str(e)}'}), 500

if __name__ == '__main__':
    # Create necessary folders with graceful error handling
    try:
        os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
        os.makedirs(app.config['OUTPUT_FOLDER'], exist_ok=True)
        print("✅ Folder uploads dan output berhasil dibuat/diverifikasi")
    except PermissionError as e:
        print("\n⚠️  WARNING: Permission issue detected!")
        print("   Folder mungkin tidak bisa dibuat, tapi aplikasi akan tetap jalan.")
        print("   Jika upload file error, coba jalankan sebagai Administrator.\n")
    except Exception as e:
        print(f"\n⚠️  WARNING: {e}")
        print("   Aplikasi akan tetap berjalan...\n")
    
    print("=" * 60)
    print("🎓 GENERATOR SERTIFIKAT PENDIDIK - STK YAKOBUS MERAUKE")
    print("   VERSION 1.3 - FINAL (FINE-TUNED)")
    print("=" * 60)
    print("\n✅ Aplikasi berjalan di: http://localhost:5000")
    print("📝 Buka browser dan akses URL di atas")
    print("\n⚠️  Tekan CTRL+C untuk menghentikan aplikasi")
    print("=" * 60 + "\n")
    
    app.run(debug=True, host='0.0.0.0', port=5000)
