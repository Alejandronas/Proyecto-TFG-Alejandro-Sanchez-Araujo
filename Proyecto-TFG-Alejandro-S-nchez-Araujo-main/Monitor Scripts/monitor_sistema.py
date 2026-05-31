import tkinter as tk
from tkinter import filedialog
import subprocess
import os

SCRIPTS = os.path.join(os.path.dirname(os.path.abspath(__file__)), "scripts")





def ejecutar(script, *args):
    comando = ["bash", SCRIPTS + "/" + script]
    for argumento in args:
        comando.append(str(argumento))

    resultado = subprocess.run(comando, capture_output=True, text=True)

    area.delete("1.0", tk.END)
    salida_normal = resultado.stdout
    errores = resultado.stderr
    area.insert(tk.END, salida_normal + errores)




def ejecutar_en_vivo(script, *args):
    comando = ["bash", SCRIPTS + "/" + script]
    for argumento in args:
        comando.append(str(argumento))

    area.delete("1.0", tk.END)
    area.insert(tk.END, "$ bash " + script + "\n")
    area.insert(tk.END, "========================================================\n")

    proceso = subprocess.Popen(comando, stdout=subprocess.PIPE,
                               stderr=subprocess.STDOUT, text=True)

    while True:
        linea = proceso.stdout.readline()
        if linea == "":
            break
        area.insert(tk.END, linea)
        area.see(tk.END)
        area.update()

    proceso.wait()
    codigo_de_salida = proceso.returncode
    area.insert(tk.END, "\n========================================================\n")
    area.insert(tk.END, "Finalizado (codigo de salida: " + str(codigo_de_salida) + ")\n")




def cpu():         ejecutar("cpu.sh")
def ram():         ejecutar("ram.sh")
def temperatura(): ejecutar("temperatura.sh")
def trafico():     ejecutar("trafico_red.sh")
def puertos():     ejecutar("auditoria_puertos.sh")
def logs():        ejecutar("logs_crecimiento.sh")

def vagrant_estado():  ejecutar("vagrant_estado.sh")
def servicios_web():   ejecutar("servicios_web.sh")

def vagrant_up():        ejecutar_en_vivo("vagrant_control.sh", entrada_dir.get(), "up",        entrada_vm.get())
def vagrant_halt():      ejecutar_en_vivo("vagrant_control.sh", entrada_dir.get(), "halt",      entrada_vm.get())
def vagrant_provision(): ejecutar_en_vivo("vagrant_control.sh", entrada_dir.get(), "provision", entrada_vm.get())
def vagrant_status():    ejecutar_en_vivo("vagrant_control.sh", entrada_dir.get(), "status",    entrada_vm.get())

def ssh_config():
    ejecutar_en_vivo("vagrant_ssh_config.sh", entrada_dir.get(), entrada_vm.get())

def abrir_terminal_ssh():
    cmd = f'cd "{entrada_dir.get()}" && vagrant ssh {entrada_vm.get()}'
    os.system(f"x-terminal-emulator -e bash -c '{cmd}; exec bash' &")

def ping():
    texto = entrada_hosts.get("1.0", tk.END)
    lineas = texto.splitlines()
    hosts = []
    for h in lineas:
        h = h.strip()
        if h != "":
            hosts.append(h)

    paquetes = entrada_paq.get().strip()
    if paquetes == "":
        paquetes = "4"

    ejecutar_en_vivo("ping_hosts.sh", paquetes, *hosts)

def discos():
    ejecutar_en_vivo("discos_vdi.sh", entrada_dir.get())

def login_fallidos():
    lineas = entrada_lineas.get().strip()
    if lineas == "":
        lineas = "50"
    ejecutar_en_vivo("login_fallidos.sh", lineas)

def seleccionar_directorio():
    ruta = filedialog.askdirectory()
    if ruta:
        entrada_dir.delete(0, tk.END)
        entrada_dir.insert(0, ruta)


root = tk.Tk()
root.title("Monitor de Sistema - ASIR")
root.geometry("900x700")

contenedor_izq = tk.Frame(root)
contenedor_izq.pack(side=tk.LEFT, fill=tk.Y, padx=8, pady=8)

canvas_izq = tk.Canvas(contenedor_izq, width=180)
canvas_izq.pack(side=tk.LEFT, fill=tk.Y)

scrollbar_izq = tk.Scrollbar(contenedor_izq, orient=tk.VERTICAL, command=canvas_izq.yview)
scrollbar_izq.pack(side=tk.RIGHT, fill=tk.Y)
canvas_izq.config(yscrollcommand=scrollbar_izq.set)

panel_botones = tk.Frame(canvas_izq)
canvas_izq.create_window((0, 0), window=panel_botones, anchor="nw")
panel_botones.bind("<Configure>", lambda e: canvas_izq.config(scrollregion=canvas_izq.bbox("all")))

panel_salida = tk.Frame(root)
panel_salida.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=8, pady=8)

scrollbar = tk.Scrollbar(panel_salida)
scrollbar.pack(side=tk.RIGHT, fill=tk.Y)

area = tk.Text(panel_salida, yscrollcommand=scrollbar.set, wrap=tk.NONE)
area.pack(fill=tk.BOTH, expand=True)
scrollbar.config(command=area.yview)

W = 20

tk.Label(panel_botones, text="CPU / RAM / TEMP").pack(pady=(10, 2))
tk.Button(panel_botones, text="CPU",         width=W, command=cpu).pack(pady=2)
tk.Button(panel_botones, text="RAM / Swap",  width=W, command=ram).pack(pady=2)
tk.Button(panel_botones, text="Temperatura", width=W, command=temperatura).pack(pady=2)

tk.Label(panel_botones, text="VAGRANT").pack(pady=(14, 2))
tk.Button(panel_botones, text="Estado VMs",    width=W, command=vagrant_estado).pack(pady=2)
tk.Button(panel_botones, text="Servicios Web", width=W, command=servicios_web).pack(pady=2)
tk.Button(panel_botones, text="SSH Config",    width=W, command=ssh_config).pack(pady=2)
tk.Button(panel_botones, text="Abrir SSH",     width=W, command=abrir_terminal_ssh).pack(pady=2)
tk.Button(panel_botones, text="vagrant up",    width=W, command=vagrant_up).pack(pady=2)
tk.Button(panel_botones, text="vagrant halt",  width=W, command=vagrant_halt).pack(pady=2)
tk.Button(panel_botones, text="provision",     width=W, command=vagrant_provision).pack(pady=2)
tk.Button(panel_botones, text="status",        width=W, command=vagrant_status).pack(pady=2)

tk.Label(panel_botones, text="Directorio Vagrant:").pack(pady=(10, 0))
entrada_dir = tk.Entry(panel_botones, width=22)
entrada_dir.insert(0, os.path.expanduser("~"))
entrada_dir.pack(pady=2)
tk.Button(panel_botones, text="Examinar", width=W, command=seleccionar_directorio).pack(pady=2)

tk.Label(panel_botones, text="Nombre VM:").pack(pady=(6, 0))
entrada_vm = tk.Entry(panel_botones, width=22)
entrada_vm.pack(pady=2)

tk.Label(panel_botones, text="RED").pack(pady=(14, 2))
tk.Button(panel_botones, text="Trafico", width=W, command=trafico).pack(pady=2)
tk.Button(panel_botones, text="Puertos", width=W, command=puertos).pack(pady=2)
tk.Button(panel_botones, text="Ping",    width=W, command=ping).pack(pady=2)

tk.Label(panel_botones, text="Hosts (uno por línea):").pack(pady=(6, 0))
entrada_hosts = tk.Text(panel_botones, height=4, width=22)
entrada_hosts.insert(tk.END, "8.8.8.8\n1.1.1.1\ngoogle.com")
entrada_hosts.pack(pady=2)

tk.Label(panel_botones, text="Paquetes:").pack(pady=(4, 0))
entrada_paq = tk.Entry(panel_botones, width=22)
entrada_paq.insert(0, "4")
entrada_paq.pack(pady=2)

tk.Label(panel_botones, text="ALMACENAMIENTO").pack(pady=(14, 2))
tk.Button(panel_botones, text="Discos / VDI",   width=W, command=discos).pack(pady=2)
tk.Button(panel_botones, text="Logs",           width=W, command=logs).pack(pady=2)

tk.Label(panel_botones, text="Líneas log:").pack(pady=(6, 0))
entrada_lineas = tk.Entry(panel_botones, width=22)
entrada_lineas.insert(0, "50")
entrada_lineas.pack(pady=2)
tk.Button(panel_botones, text="Login Fallidos", width=W, command=login_fallidos).pack(pady=2)

root.mainloop()