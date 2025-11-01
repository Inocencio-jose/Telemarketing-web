import os

estrutura = {
    "index.php": "",
    "admin": {
        "index.php": "",
        "novo.php": "",
        "editar.php": "",
        "excluir.php": "",
        "salvar.php": "",
    },
    "api": {
        "get_roteiro.php": "",
    },
    "config": {
        "db.php": "",
    },
    "videos": {
        "apresentador.mp4": "",  # arquivo fictício
    },
    "db.sql": "",
    "README.md": "# Telejornal Web\n\nSistema de telejornal online com painel de administração."
}

def criar_estrutura(base_path, estrutura):
    for nome, conteudo in estrutura.items():
        caminho = os.path.join(base_path, nome)
        if isinstance(conteudo, dict):
            os.makedirs(caminho, exist_ok=True)
            criar_estrutura(caminho, conteudo)
        else:
            with open(caminho, "w", encoding="utf-8") as f:
                f.write(conteudo)

# Executar na pasta atual
base_dir = os.getcwd()
criar_estrutura(base_dir, estrutura)

print("✅ Estrutura de pastas e arquivos criada com sucesso!")