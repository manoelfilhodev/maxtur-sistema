
# 🚛 Maxtur Sistema de Gestão de Frotas (Sanctum)

Sistema desenvolvido para gestão de frotas, operações e controle logístico da empresa **Maxtur**.

## ✅ Funcionalidades

- Controle de usuários com níveis de acesso (Admin, Operador, Gestor)
- Cadastro de veículos e motoristas
- Controle de abastecimentos
- Controle de manutenções preventivas e corretivas
- Gestão de viagens e operações
- Dashboard com indicadores de frota
- Relatórios detalhados em PDF e Excel
- Sistema de notificações e alertas operacionais
- Logs de atividades

## 🚀 Tecnologias Utilizadas

- 🔥 **PHP** 8+
- 🛠️ **Laravel** 10+
- 🎨 **Blade Template Engine**
- 🗄️ **MySQL/MariaDB**
- 📊 **Chart.js** ou **ApexCharts** (para gráficos)
- 💅 **Bootstrap**, **Tailwind CSS** ou framework do template (Mazer, Hyper, etc.)
- 🐙 **Git** e **GitHub** para versionamento
- 🐧 **Ubuntu VPS** (ou outro ambiente Linux)

## ⚙️ Instalação do Projeto

### ✔️ Pré-requisitos

- PHP >= 8.1
- Composer
- MySQL ou MariaDB
- Git
- Extensões PHP: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo

### ✔️ Clonando o Projeto

```bash
git clone https://github.com/manoelfilhodev/maxtur-sistema.git .
cd maxtur-sistema
```

### ✔️ Instalar dependências

```bash
composer install
```

### ✔️ Configurar o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Configure o arquivo `.env` com as credenciais do banco de dados.

### ✔️ Rodar as migrations e seeders

```bash
php artisan migrate --seed
```

### ✔️ Permissões de pastas

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### ✔️ Rodar o servidor local (opcional)

```bash
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

---

## 🔐 Acesso Padrão (Seeder)

| Usuário              | Senha       | Role      |
| -------------------- | ----------- | --------- |
| admin@maxtur.com     | admin123    | Admin     |
| operador@maxtur.com  | operador123 | Operador  |
| gestor@maxtur.com    | gestor123   | Gestor    |

> ⚠️ **Altere essas credenciais após o primeiro acesso!**

---

## 🛠️ Estrutura do Projeto

```
/app
/bootstrap
/config
/database
/public
/resources
/routes
/storage
/tests
```

---

## 📄 Licença

Este projeto é de uso interno da **Maxtur Transportes**.

---

## 🤝 Desenvolvido por

**Manoel Filho**  
[Systex Sistemas Inteligentes](https://systex.com.br)  
📧 manoel.filho.mf@gmail.com  
🚀 [LinkedIn](https://linkedin.com/in/seu-usuario) | [GitHub](https://github.com/manoelfilhodev)
