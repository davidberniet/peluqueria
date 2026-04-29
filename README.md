# 💇‍♂️ Venus - Gestión de Peluquería

**Venus** es una plataforma integral de gestión para peluquerías y centros de estética, diseñada para optimizar la reserva de citas, la gestión de inventario y la comunicación con el cliente. Construida sobre el framework **Symfony**, ofrece una experiencia fluida tanto para administradores como para clientes.

---

## 🚀 Características Principales

### 👤 Área del Cliente
*   **Registro y Seguridad**: Sistema de autenticación seguro con soporte para **Autenticación en Dos Pasos (2FA)**.
*   **Gestión de Perfil**: Los usuarios pueden actualizar sus datos personales y cambiar su contraseña de forma segura.
*   **Reserva de Citas**: Interfaz intuitiva para seleccionar servicios, profesionales y horarios disponibles en tiempo real.
*   **Historial de Citas**: Panel personal para consultar citas pasadas y próximas.
*   **Catálogo de Productos y Servicios**: Visualización detallada de los servicios ofrecidos y productos a la venta.
*   **Notificaciones**: Recepción automática de recordatorios de citas por correo electrónico.

### 🛠️ Panel de Administración (Backoffice)
*   **Dashboard Estadístico**: Visualización en tiempo real de:
    *   Citas programadas para el día.
    *   Ingresos diarios estimados.
    *   Total de clientes registrados.
*   **Gestión de Calendario**:
    *   **Días Bloqueados**: Configuración de festivos, vacaciones o días de cierre.
    *   **Reglas de Horario**: Definición de horarios de apertura, cierre e intervalos de tiempo para las citas.
*   **Control de Citas**: Listado completo de todas las reservas con capacidad para confirmar o cancelar citas manualmente.
*   **Gestión de Servicios**: Catálogo dinámico para crear, editar o desactivar servicios (cortes, tintes, tratamientos, etc.).
*   **Inventario de Productos**: Gestión de productos con soporte para subida de imágenes y descripción.

---

## 🏛️ Arquitectura y Diseño

La aplicación sigue el patrón de diseño **MVC (Modelo-Vista-Controlador)**, el estándar de la industria para aplicaciones web robustas:

*   **Modelo (Model)**: Gestionado a través de **Doctrine ORM**. Las entidades en `src/Entity` definen la estructura de los datos y las relaciones entre ellos.
*   **Vista (View)**: Implementada con el motor de plantillas **Twig**. Permite una separación clara entre la lógica de negocio y la presentación visual.
*   **Controlador (Controller)**: Los controladores en `src/Controller` actúan como intermediarios, procesando las peticiones del usuario, interactuando con el modelo y devolviendo la vista adecuada.

---

## 🗄️ Modelo de Datos (Base de Datos)

El sistema utiliza una base de datos relacional con las siguientes entidades principales:

### 👤 Usuario (`User`)
Almacena la información de clientes y administradores.
*   **Atributos**: `email`, `roles`, `password`, `nombre`, `telefono`, `authCode` (para 2FA).
*   **Relaciones**: Un usuario puede tener muchas citas (`OneToMany`).

### 📅 Cita (`Cita`)
Representa una reserva en el centro.
*   **Atributos**: `fechaInicio`, `fechaFin`, `estado` (Pendiente, Confirmada, Cancelada), `notas`.
*   **Relaciones**: 
    *   Pertenece a un `User` (Cliente).
    *   Puede tener múltiples `Servicios` (`ManyToMany`).
    *   Opcionalmente asignada a un `User` (Empleado).

### ✂️ Servicio (`Servicio`)
Define los servicios ofrecidos (ej: Corte, Tinte).
*   **Atributos**: `nombre`, `descripcion`, `precio`, `duracion`, `activo`.

### 🧴 Producto (`Producto`)
Productos disponibles para la venta.
*   **Atributos**: `nombre`, `descripcion`, `precio`, `imagen`, `activo`.

### 🏢 Local y Configuración (`Local`, `Horario`, `ReglaHorario`, `DiaBloqueado`)
Gestionan la disponibilidad del negocio.
*   **Local**: Datos del centro (nombre, dirección).
*   **Horario**: Franjas generales de apertura/cierre.
*   **ReglaHorario**: Reglas específicas por día de la semana.
*   **DiaBloqueado**: Fechas específicas donde el centro está cerrado.

---

## 🛠️ Stack Tecnológico

*   **Backend**: [Symfony 7+](https://symfony.com/)
*   **Base de Datos**: MySQL / MariaDB (vía Doctrine ORM)
*   **Frontend**: Twig, JavaScript (Stimulus / Turbo) y CSS Moderno.
*   **Seguridad**: Symfony Security Bundle + Scheb TwoFactorBundle.
*   **Infraestructura**: Docker & Docker Compose.

---

## 📦 Instalación y Despliegue

### Requisitos previos
*   Docker y Docker Compose instalados.

### Pasos para arrancar el proyecto
1.  **Clonar el repositorio**:
    ```bash
    git clone <url-del-repositorio>
    cd peluqueria
    ```

2.  **Levantar el entorno con Docker**:
    ```bash
    docker compose up -d
    ```

3.  **Instalar dependencias de PHP**:
    ```bash
    docker compose exec php composer install
    ```

4.  **Ejecutar migraciones**:
    ```bash
    docker compose exec php bin/console doctrine:migrations:migrate
    ```

5.  **Acceder a la aplicación**:
    La aplicación estará disponible en `http://localhost:8080` (o el puerto configurado en tu `compose.yaml`).

---

## ⏰ Tareas Programadas

El sistema incluye un comando para el envío de recordatorios automáticos:
```bash
php bin/console app:enviar-recordatorios
```
*Se recomienda configurar un Cron job para ejecutar este comando diariamente.*

---

## 🎨 Diseño y UX
La aplicación cuenta con una estética premium, utilizando gradientes modernos, micro-animaciones y un diseño totalmente responsivo para que los clientes puedan reservar cómodamente desde sus dispositivos móviles.

---
*Desarrollado con ❤️ para la gestión moderna de centros de belleza.*
