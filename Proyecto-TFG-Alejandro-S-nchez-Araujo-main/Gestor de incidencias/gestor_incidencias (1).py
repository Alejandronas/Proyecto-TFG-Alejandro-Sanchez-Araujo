import tkinter as tk
from tkinter import ttk, messagebox
import csv
import os
from datetime import datetime

CSV_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "incidencias.csv")
CAMPOS   = ["id", "empleado", "departamento", "equipo", "descripcion",
            "prioridad", "estado", "tecnico", "creada", "actualizada"]

PRIORIDADES   = ["Alta", "Media", "Baja"]
ESTADOS       = ["Abierta", "En progreso", "Resuelta", "Cerrada"]
DEPARTAMENTOS = ["Administracion", "Urgencias", "Cirugia", "Pediatria",
                 "Radiologia", "Laboratorio", "Farmacia", "RRHH", "Otro"]
EQUIPOS       = ["Ordenador", "Impresora", "Escaner", "Monitor",
                 "Telefono", "Red / Wifi", "Software", "Otro"]


def leer():
    if not os.path.exists(CSV_PATH):
        return []
    with open(CSV_PATH, newline="", encoding="utf-8") as f:
        return list(csv.DictReader(f))

def guardar(filas):
    with open(CSV_PATH, "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=CAMPOS)
        w.writeheader()
        w.writerows(filas)

def ahora():
    return datetime.now().strftime("%d/%m/%Y %H:%M")

def siguiente_id():
    mayor = 0
    for fila in leer():
        if int(fila["id"]) > mayor:
            mayor = int(fila["id"])
    return str(mayor + 1)

def buscar(id_):
    for fila in leer():
        if fila["id"] == str(id_):
            return fila
    return None

def tecnico_o_guion(fila):
    if fila["tecnico"] == "":
        return "-"
    return fila["tecnico"]

def crear(empleado, depto, equipo, desc, prioridad):
    filas = leer()
    filas.append({
        "id":           siguiente_id(),
        "empleado":     empleado,
        "departamento": depto,
        "equipo":       equipo,
        "descripcion":  desc,
        "prioridad":    prioridad,
        "estado":       "Abierta",
        "tecnico":      "",
        "creada":       ahora(),
        "actualizada":  ahora()
    })
    guardar(filas)

def actualizar(id_, estado, tecnico):
    filas = leer()
    for fila in filas:
        if fila["id"] == str(id_):
            fila["estado"]      = estado
            fila["tecnico"]     = tecnico
            fila["actualizada"] = ahora()
    guardar(filas)

def eliminar(id_):
    filas = leer()
    resultado = []
    for fila in filas:
        if fila["id"] != str(id_):
            resultado.append(fila)
    guardar(resultado)

def listar(estado="Todos", prioridad="Todos"):
    resultado = []
    for fila in leer():
        if estado != "Todos" and fila["estado"] != estado:
            continue
        if prioridad != "Todos" and fila["prioridad"] != prioridad:
            continue
        resultado.append(fila)

    orden = {"Alta": 0, "Media": 1, "Baja": 2}
    resultado.sort(key=lambda fila: orden.get(fila["prioridad"], 3))
    return resultado


def nueva_incidencia():
    nombre = entrada_nombre.get().strip()
    desc   = entrada_desc.get("1.0", tk.END).strip()

    if not nombre or not desc:
        messagebox.showwarning("Aviso", "El nombre y la descripcion son obligatorios.")
        return

    crear(nombre, combo_depto.get(), combo_equipo.get(), desc, combo_prior.get())
    mostrar("Incidencia registrada correctamente.\n\nEmpleado:  " + nombre +
            "\nDepto:     " + combo_depto.get() +
            "\nEquipo:    " + combo_equipo.get() +
            "\nPrioridad: " + combo_prior.get())

    entrada_nombre.delete(0, tk.END)
    entrada_desc.delete("1.0", tk.END)
    combo_depto.current(0)
    combo_equipo.current(0)
    combo_prior.current(0)

def ver_todas():
    filas = listar(combo_filtro_estado.get(), combo_filtro_prior.get())
    if not filas:
        mostrar("No hay incidencias con los filtros seleccionados.")
        return

    lineas = "INCIDENCIAS (" + str(len(filas)) + ")\n" + "=" * 56 + "\n"
    for fila in filas:
        lineas += "\n"
        lineas += "#" + fila["id"] + " [" + fila["prioridad"] + "] " + fila["estado"] + "\n"
        lineas += "Empleado:  " + fila["empleado"] + " (" + fila["departamento"] + ")\n"
        lineas += "Equipo:    " + fila["equipo"] + "\n"
        lineas += "Tecnico:   " + tecnico_o_guion(fila) + "\n"
        lineas += "Creada:    " + fila["creada"] + "\n"
        lineas += "Descripcion: " + fila["descripcion"] + "\n"
        lineas += "-" * 40 + "\n"

    mostrar(lineas)

def incidencia_pedida():
    id_ = entrada_id.get().strip()
    if not id_:
        mostrar("Introduce un ID en el campo correspondiente.")
        return None, None

    fila = buscar(id_)
    if fila is None:
        mostrar("No se encontro ninguna incidencia con ID " + id_)
        return None, None

    return id_, fila

def ver_detalle():
    id_, fila = incidencia_pedida()
    if fila is None:
        return

    texto  = "DETALLE INCIDENCIA #" + fila["id"] + "\n"
    texto += "=" * 56 + "\n"
    texto += "Empleado:     " + fila["empleado"] + "\n"
    texto += "Departamento: " + fila["departamento"] + "\n"
    texto += "Equipo:       " + fila["equipo"] + "\n"
    texto += "Prioridad:    " + fila["prioridad"] + "\n"
    texto += "Estado:       " + fila["estado"] + "\n"
    texto += "Tecnico:      " + tecnico_o_guion(fila) + "\n"
    texto += "Creada:       " + fila["creada"] + "\n"
    texto += "Actualizada:  " + fila["actualizada"] + "\n"
    texto += "\nDescripcion:\n" + fila["descripcion"] + "\n"

    mostrar(texto)

def gestionar():
    id_, fila = incidencia_pedida()
    if fila is None:
        return

    win = tk.Toplevel(root)
    win.title("Gestionar #" + id_)
    win.resizable(False, False)
    win.grab_set()

    titulo = "#" + id_ + "  " + fila["equipo"] + " — " + fila["empleado"]
    tk.Label(win, text=titulo, font=("", 10, "bold")).grid(
        row=0, column=0, columnspan=2, sticky="w", padx=12, pady=(14, 8))

    tk.Label(win, text="Estado:").grid(row=1, column=0, sticky="w", padx=12, pady=6)
    combo_estado_win = ttk.Combobox(win, values=ESTADOS, state="readonly", width=18)
    combo_estado_win.set(fila["estado"])
    combo_estado_win.grid(row=1, column=1, sticky="w", padx=12, pady=6)

    tk.Label(win, text="Tecnico:").grid(row=2, column=0, sticky="w", padx=12, pady=6)
    entry_tec = tk.Entry(win, width=20)
    entry_tec.insert(0, fila["tecnico"])
    entry_tec.grid(row=2, column=1, sticky="w", padx=12, pady=6)

    def guardar_cambios():
        actualizar(id_, combo_estado_win.get(), entry_tec.get().strip())
        win.destroy()
        ver_detalle()

    fb = tk.Frame(win)
    fb.grid(row=3, column=0, columnspan=2, pady=12)
    tk.Button(fb, text="Cancelar", command=win.destroy).pack(side="left", padx=6)
    tk.Button(fb, text="Guardar",  command=guardar_cambios).pack(side="left", padx=6)

def eliminar_incidencia():
    id_, fila = incidencia_pedida()
    if fila is None:
        return

    if messagebox.askyesno("Confirmar", "Eliminar incidencia #" + id_ + "?"):
        eliminar(id_)
        mostrar("Incidencia #" + id_ + " eliminada.")
        entrada_id.delete(0, tk.END)

def mostrar(texto):
    area.delete("1.0", tk.END)
    area.insert(tk.END, texto)
    area.see("1.0")


root = tk.Tk()
root.title("Gestor de Incidencias - Clinica")
root.geometry("900x700")

contenedor_izq = tk.Frame(root)
contenedor_izq.pack(side=tk.LEFT, fill=tk.Y, padx=8, pady=8)

canvas_izq = tk.Canvas(contenedor_izq, width=190)
canvas_izq.pack(side=tk.LEFT, fill=tk.Y, expand=False)

scrollbar_izq = tk.Scrollbar(contenedor_izq, orient=tk.VERTICAL, command=canvas_izq.yview)
scrollbar_izq.pack(side=tk.RIGHT, fill=tk.Y)

canvas_izq.config(yscrollcommand=scrollbar_izq.set)

panel_botones = tk.Frame(canvas_izq)
canvas_izq.create_window((0, 0), window=panel_botones, anchor="nw")

def actualizar_scroll(event):
    canvas_izq.config(scrollregion=canvas_izq.bbox("all"))

panel_botones.bind("<Configure>", actualizar_scroll)

panel_salida = tk.Frame(root)
panel_salida.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=8, pady=8)

scrollbar = tk.Scrollbar(panel_salida)
scrollbar.pack(side=tk.RIGHT, fill=tk.Y)

area = tk.Text(panel_salida, yscrollcommand=scrollbar.set, wrap=tk.NONE)
area.pack(fill=tk.BOTH, expand=True)
scrollbar.config(command=area.yview)

tk.Label(panel_botones, text="NUEVA INCIDENCIA").pack(pady=(10, 2))

tk.Label(panel_botones, text="Nombre:").pack(pady=(6, 0))
entrada_nombre = tk.Entry(panel_botones, width=24)
entrada_nombre.pack(pady=2)

tk.Label(panel_botones, text="Departamento:").pack(pady=(4, 0))
combo_depto = ttk.Combobox(panel_botones, values=DEPARTAMENTOS, state="readonly", width=22)
combo_depto.current(0)
combo_depto.pack(pady=2)

tk.Label(panel_botones, text="Equipo afectado:").pack(pady=(4, 0))
combo_equipo = ttk.Combobox(panel_botones, values=EQUIPOS, state="readonly", width=22)
combo_equipo.current(0)
combo_equipo.pack(pady=2)

tk.Label(panel_botones, text="Prioridad:").pack(pady=(4, 0))
combo_prior = ttk.Combobox(panel_botones, values=PRIORIDADES, state="readonly", width=22)
combo_prior.current(0)
combo_prior.pack(pady=2)

tk.Label(panel_botones, text="Descripcion:").pack(pady=(4, 0))
entrada_desc = tk.Text(panel_botones, height=4, width=24)
entrada_desc.pack(pady=2)

tk.Button(panel_botones, text="Registrar incidencia", width=22, command=nueva_incidencia).pack(pady=(6, 2))

tk.Label(panel_botones, text="VER INCIDENCIAS").pack(pady=(14, 2))

tk.Label(panel_botones, text="Filtrar estado:").pack(pady=(4, 0))
combo_filtro_estado = ttk.Combobox(panel_botones, values=["Todos"] + ESTADOS, state="readonly", width=22)
combo_filtro_estado.current(0)
combo_filtro_estado.pack(pady=2)

tk.Label(panel_botones, text="Filtrar prioridad:").pack(pady=(4, 0))
combo_filtro_prior = ttk.Combobox(panel_botones, values=["Todos"] + PRIORIDADES, state="readonly", width=22)
combo_filtro_prior.current(0)
combo_filtro_prior.pack(pady=2)

tk.Button(panel_botones, text="Ver todas", width=22, command=ver_todas).pack(pady=(6, 2))

tk.Label(panel_botones, text="GESTIONAR POR ID").pack(pady=(14, 2))

tk.Label(panel_botones, text="ID incidencia:").pack(pady=(4, 0))
entrada_id = tk.Entry(panel_botones, width=24)
entrada_id.pack(pady=2)

tk.Button(panel_botones, text="Ver detalle",  width=22, command=ver_detalle).pack(pady=2)
tk.Button(panel_botones, text="Gestionar",    width=22, command=gestionar).pack(pady=2)
tk.Button(panel_botones, text="Eliminar",     width=22, command=eliminar_incidencia, fg="red").pack(pady=2)

root.mainloop()