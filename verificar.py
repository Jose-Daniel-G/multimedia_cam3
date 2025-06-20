from flask import Flask, jsonify
import os
import shutil
import pandas as pd
import traceback
import json
app = Flask(__name__)

# Configuración de rutas
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
RUTA_CARPETA = os.path.join(BASE_DIR, "departamento")
DESTINO = os.path.join(RUTA_CARPETA, "archivo")
COLUMNA_NOMBRE = "nom_con"

def verificar_pdfs_en_csv():
    try:
        # Verificar si la carpeta existe
        if not os.path.exists(RUTA_CARPETA):
            return "Error: La carpeta 'departamento' no existe."

        archivos = set(os.listdir(RUTA_CARPETA))
        archivos_csv = {archivo for archivo in archivos if archivo.endswith('.csv')}
        archivos_pdf = {pdf.lower().strip() for pdf in archivos if pdf.endswith('.pdf')}

        if not archivos_csv:
            return {"Error: No se encontró ningún archivo CSV en la carpeta."}
            # return "Error: No se encontró ningún archivo CSV en la carpeta."
        if len(archivos_csv) > 1:
             return {"Error: Solo debe haber un archivo CSV en la carpeta."}
            # return "Error: Solo debe haber un archivo CSV en la carpeta."

        if not archivos_pdf:  # En lugar de `len(archivos_pdf) < 1`
            return {"Error: No hay suficientes archivos PDF."}
            # return "Error: No hay suficientes archivos PDF."

        archivo_csv = os.path.join(RUTA_CARPETA, next(iter(archivos_csv)))
        df = pd.read_csv(archivo_csv, dtype=str)

        # Validar que la columna necesaria esté presente en el CSV
        if COLUMNA_NOMBRE not in df.columns:
            return {f"Error: El archivo CSV no contiene la columna '{COLUMNA_NOMBRE}'."}
            # return f"Error: El archivo CSV no contiene la columna '{COLUMNA_NOMBRE}'."

        nombres_validos = set(df[COLUMNA_NOMBRE].dropna().str.lower().str.strip())

        # Verificar si los PDFs están en la lista del CSV
        pdfs_faltantes = {nombre for nombre in nombres_validos if f"{nombre}.pdf" not in archivos_pdf}
        pdf_no_encontrados = [pdf for pdf in archivos_pdf if pdf.replace('.pdf', '') not in nombres_validos]

        if pdf_no_encontrados:
            # return "Error: Los siguientes PDFs no estan en el CSV "{.join(f" - {pdf}" for pdf in pdf_no_encontrados)}
            return "Error: Los siguientes PDFs no estan en el CSV: "{join(f" - {pdf}" for pdf in pdf_no_encontrados)}

            # return "Error: Los siguientes PDFs no están en el CSV:\n" + "\n".join(f" - {pdf}" for pdf in pdf_no_encontrados)

        if pdfs_faltantes:
            return "Error: Faltan los siguientes PDFs:   ".join(list(pdfs_faltantes))        
            # return "Error: Faltan los siguientes PDFs:\n" + "\n".join(f" - {pdf}" for pdf in pdfs_faltantes)

        # Crear la carpeta de destino si no existe
        os.makedirs(DESTINO, exist_ok=True)

        for pdf in archivos_pdf:
            shutil.move(os.path.join(RUTA_CARPETA, pdf), os.path.join(DESTINO, pdf))

        return {"success": True,"Éxito: Todos los archivos PDF fueron movidos correctamente."}
        # return "Exito: Todos los archivos PDF fueron movidos correctamente."

    except Exception as e:
        return {"Error inesperado.", "detalle": str(e), "traceback": traceback.format_exc()}        
        # return f"Error inesperado: {e}\n{traceback.format_exc()}"

# Para ejecutar como servicio con Flask
@app.route("/verificar", methods=["GET"])
def verificar():
    resultado = verificar_pdfs_en_csv()
    return jsonify({"mensaje": resultado})

if __name__ == "__main__":
    print(verificar_pdfs_en_csv())
