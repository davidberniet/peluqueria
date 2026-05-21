# Enunciado de Entrega Final y Defensa del

# TFG

**Autor:** Manuel Prieto Macias

## Introducción y Normativa General

Este documento establece los requisitos definitivos y de obligado cumplimiento para la
entrega final y defensa del Trabajo de Fin de Grado (TFG). Tras la validación del prototipo
arquitectónico y los módulos iniciales, se exige la presentación de un producto de
software completo, funcional y desplegable.

La evaluación será **estricta**. No se admitirán proyectos que no cumplan con los
estándares de calidad profesional exigidos en el ciclo formativo. Un fallo crítico en la
arquitectura, la seguridad o la presentación supondrá la no superación de esta fase.

## 1. Requisitos Técnicos del Proyecto

El proyecto debe ser una aplicación web completa, desarrollada bajo los siguientes
estándares tecnológicos innegociables:

### 1.1. Arquitectura y Backend (Symfony)

El núcleo de la aplicación debe estar construido sobre el framework Symfony, respetando
el patrón MVC y las buenas prácticas de desarrollo.

```
Controladores y Rutas: Uso adecuado de atributos #[Route]. Los controladores
deben ser ligeros, delegando la lógica de negocio a servicios cuando sea necesario.
Modelo de Datos (Doctrine): Las entidades deben reflejar fielmente el Modelo
Entidad-Relación (E/R) final. Se exige el uso correcto de relaciones (OneToMany,
ManyToMany, etc.), migraciones automatizadas y validaciones a nivel de entidad
(Assert).
```

```
Seguridad: Implementación robusta del componente de seguridad de Symfony. Las
contraseñas deben estar encriptadas (hashing). Se valorará la correcta gestión de
roles y permisos (ej. ROLE_USER, ROLE_ADMIN).
```

### 1.2. Frontend y Diseño (Tailwind CSS y Framework de Elección)

La interfaz de usuario debe ser profesional, responsiva y coherente.

```
Framework de Frontend: Se permite el uso de frameworks modernos como React,
Vue, Angular, o un motor de plantillas tradicional como Twig. La elección debe estar
justificada en la memoria técnica. Se exige una estructura de componentes modular
y reutilizable, o una estructura jerárquica de plantillas con un base.html.twig
sólido y bloques bien definidos, según el framework elegido.
Estilos: Integración completa de Tailwind CSS. No se admitirán estilos en línea ni
archivos CSS desorganizados. El diseño debe adaptarse perfectamente a
dispositivos móviles, tablets y escritorio (Mobile First).
Experiencia de Usuario (UX): La navegación debe ser intuitiva. Los formularios
deben contar con validación tanto en el cliente (HTML5/JS) como en el servidor
(Symfony Forms).
```

### 1.3. Entorno y Despliegue (Docker)

El proyecto debe ser 100% reproducible en cualquier máquina mediante contenedores.

```
Docker Compose: Se exige un archivo docker-compose.yaml perfectamente
configurado que levante, como mínimo, el servidor web (Nginx/Apache), el
contenedor de PHP-FPM y el motor de base de datos (MySQL/PostgreSQL).
Aislamiento: La aplicación debe ejecutarse íntegramente dentro de los
contenedores. El tribunal evaluador levantará el proyecto ejecutando únicamente
docker compose up -d. Si el proyecto no levanta a la primera, se considerará
suspenso automático.
```

## 2. Documentación a Entregar

La entrega debe realizarse en un archivo comprimido (.zip o .tar.gz) que contenga el
código fuente (sin las carpetas vendor ni node_modules) y la siguiente documentación
en formato PDF:

### 2.1. Memoria Técnica del Proyecto

Un documento formal que detalle la construcción del software. Debe incluir:

```
Descripción del Proyecto: Objetivos, alcance y público objetivo.
Arquitectura del Sistema: Diagrama de arquitectura de alto nivel.
Modelo de Datos: Diagrama Entidad-Relación (E/R) final y diccionario de datos.
Especificaciones Técnicas: Tecnologías utilizadas, justificación de las mismas y
dependencias clave.
Manual de Despliegue: Instrucciones paso a paso para levantar el proyecto con
Docker, ejecutar migraciones y cargar datos de prueba (Fixtures).
```

### 2.2. Presentación para la Defensa

El archivo de la presentación (PDF o PPTX) que se utilizará durante la defensa oral. Debe
seguir las normas de formato especificadas en la sección 3.

## 3. Normativa Estricta para la Defensa Oral

La defensa del TFG es un acto académico formal. La presentación debe demostrar
dominio técnico, capacidad de síntesis y profesionalidad.

### 3.1. Formato de la Presentación

```
Duración: La exposición tendrá una duración máxima de 15 minutos , seguidos de
5-10 minutos de preguntas por parte del tribunal. El tiempo se cronometrará
estrictamente; se cortará la exposición si se excede el límite.
Estructura Obligatoria:
```

1. **Introducción (2 min):** Presentación del problema y la solución propuesta.
2. **Arquitectura y Tecnologías (3 min):** Explicación del stack tecnológico
   (Backend: Symfony; Frontend: Framework elegido, ej. React/Twig;
   Contenerización: Docker; Estilos: Tailwind CSS) y justificación de decisiones
   clave.
3. **Modelo de Datos (2 min):** Breve exposición del E/R y las entidades
   principales.

4. **Demostración Práctica / Demo (6 min):** Recorrido en vivo por la aplicación
   funcionando. Se deben mostrar los flujos principales (registro, login,
   operaciones CRUD core del proyecto).
5. **Conclusiones y Trabajo Futuro (2 min):** Lecciones aprendidas y posibles
   mejoras.

```
Diseño Visual: Las diapositivas deben tener formato 16:9. Se prohíbe el exceso de
texto; utilizad esquemas, diagramas y palabras clave. El código fuente mostrado en
las diapositivas debe ser legible, con resaltado de sintaxis y tipografía
monoespaciada.
```

### 3.2. Criterios de Evaluación del Tribunal

El tribunal evaluará con máxima exigencia los siguientes aspectos:

```
Solidez Técnica: ¿El código es limpio y sigue los estándares de Symfony? ¿La
base de datos está bien normalizada?
Funcionalidad: ¿La aplicación cumple con lo prometido en la memoria? ¿Existen
errores (bugs) evidentes durante la demo?
Despliegue: ¿El entorno Docker funciona sin intervención manual adicional?
Comunicación: ¿El alumno se expresa con claridad y propiedad técnica? ¿Sabe
defender sus decisiones ante las preguntas del tribunal?
```

## Conclusión

Esta entrega final representa la culminación de vuestra formación. Se espera un nivel de
excelencia acorde a un perfil Junior Full Stack Developer. Revisad exhaustivamente
vuestro código, probad el despliegue en un entorno limpio y ensayad vuestra
presentación. No habrá segundas oportunidades para fallos básicos de arquitectura o
despliegue.
