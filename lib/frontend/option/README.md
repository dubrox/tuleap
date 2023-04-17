# Option

A TypeScript implementation of the `Maybe`/`Option` type present in functional programming languages. See [ADR-0022: Option][0] for more context.

## Usage

Functions returning a value can use `Option.fromValue(...)` and `Option.nothing<TypeOfValue>()`.

Example:

```typescript
import { Option } from "@tuleap/option";

function getOptionalDataset(element: HTMLElement): Option<string> {
    const dataset = element.dataset.optional;
    if (!dataset) {
        return Option.nothing<string>();
    }
    return Option.fromValue(dataset);
}
```

You can then use the resulting option using `.apply()`:

```typescript
const option = getOptionalDataset(mount_point);
option.apply((dataset: string): void => {
   // dataset is defined, do something with it
});
```

You can transform the "inner type" (and do nothing when it is `nothing`) using `.map()`:

```typescript
type DerivedState = {
    derived_value: string;
};

const option = getOptionalDataset(mount_point);
const mapped_option = option.map((dataset: string): DerivedState => {
    return { derived_value: dataset };
});
// if option was `nothing`, mapped_option is still `nothing`.
// if option has a value, mapped_option is a new `Option<DerivedState>`.
```

At the end of a processing pipeline, you might want to retrieve the unwrapped value with `.mapOr()`:

```typescript
import { Fault } from "@tuleap/fault";
import { ok, err } from "neverthrow";
import type { Ok } from "neverthrow";

const option = getOptionalDataset(mount_point);
option.mapOr(
    (dataset: string): Ok => ok(dataset),
    err(Fault.fromMessage("Dataset is missing")),
);
```

In unit tests when the inner value is a primitive or an array, and you want to run assertions, use `.unwrapOr()`:

```typescript
const option = getOptionalDataset(mount_point);
expect(option.unwrapOr(null)).toBe("dataset-value");
```

## Links

* [ADR-0022: Option][0]

[0]: ../../../adr/0022-option.md