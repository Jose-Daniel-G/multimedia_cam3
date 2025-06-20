import os
from reportlab.pdfgen import canvas
import pandas as pd
import re

# Ruta al archivo Excel
archivo_excel = "Ejemplo.de.plantilla.xlsx"

# Carpeta donde guardar los PDF
carpeta_salida = "Ejemplo.de.plantilla"
os.makedirs(carpeta_salida, exist_ok=True)

# Leer el archivo Excel indicando que el encabezado está en la fila 4 (índice 3)
df = pd.read_excel(archivo_excel, header=3)

# Leer los nombres de la columna 'ARC_ADJ'
nombres_pdfs = df['ARC_ADJ'].dropna().astype(str)

def limpiar_nombre_archivo(nombre):
    # Reemplaza caracteres no válidos por guiones bajos
    return re.sub(r'[<>:"/\\|?*]', '_', nombre)

# Crear un PDF vacío por cada nombre limpio
for nombre in nombres_pdfs:
    nombre_limpio = limpiar_nombre_archivo(nombre)  # sin .pdf
    ruta_pdf = os.path.join(carpeta_salida, nombre_limpio)
    c = canvas.Canvas(ruta_pdf)
    c.drawString(100, 750, f"Este es el archivo: {nombre}")
    c.save()

print(f"Se han generado {len(nombres_pdfs)} archivos PDF en la carpeta '{carpeta_salida}'.")
