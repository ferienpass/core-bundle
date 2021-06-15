# Transactional Emails

Transactional email templates built in [Maizzle](https://maizzle.com).

## Getting Started

Install the Maizzle CLI:

```sh
npm install -g @maizzle/cli
```

Develop locally:

```sh
cd src/Resources/maizzle
```

```sh
maizzle serve
```

Build for production:

```sh
maizzle build production
```

Maizzle documentation is available at https://maizzle.com

## Important notes

1. Run `npm run watch` and edit the email templates.
2. Use twig variable placeholders (`{{ }}`) only inside `<raw>` tags or prefixed with an `@`.
3. Run `npm run prod` to write production-ready html/twig templates to `src/Resources/views` folder.
