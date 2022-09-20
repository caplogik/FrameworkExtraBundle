// some properties can be updated without breaking existing data like labels and others presentation data

// allow some kind of widget property
// define better validation properties


// type Widget
// 	= { type: "input" }
// 	| { type: "choice", expanded: boolean }
// 	| {}

type Schema = {
	label: string
	note?: string
	required?: boolean
} & (
	ValueBoolean
	| ValueNumber
	| ValueString
	| ValueArray
	| ValueObject
	| ValueDateTime
)

// widget : checkbox, choice
type ValueBoolean = {
	type: "boolean"
}

// widget : number input, range, choice
type ValueNumber = {
	type: "number"
	minimum?: number
	maximum?: number
	// scale?: number
	// step?: number
}

// widget : text input, choice
type ValueString = {
	type: "string"
	minimumLength?: number
	maximumLength?: number
}

// widget : forms of sub schema, choice multiple
type ValueArray = {
	type: "array"
	items: Schema
	minimumCount?: number
	maximumCount?: number
}

// sub schema form
// can be displayed as a list of tabs ?
type ValueObject = {
	type: "object"
	properties: { [name: string]: Schema }
}

type ValueDateTime = {
	type: "datetime"
}






const form: Schema = {
	label: "Demo",
	type: "object",
	properties: {
		boolean: {
			label: "Boolean",
			type: "object",
			properties: {
				checkbox: {
					label: "Checkbox",
					type: "boolean"
				},
				choice: {
					label: "Choice",
					type: "boolean"
				}
			}
		},
		number: {
			label: "Number",
			type: "object",
			properties: {
				field: {
					label: "Field",
					type: "number"
				},
				range: {
					label: "Range",
					type: "number"
				},
				choice: {
					label: "Choice",
					type: "number"
				}
			}
		},
		string: {
			label: "String",
			type: "object",
			properties: {
				field: {
					label: "Field",
					type: "string"
				},
				choice: {
					label: "Choice",
					type: "string"
				},
			}
		},
		array: {
			label: "Array (of strings)",
			type: "object",
			properties: {
				form: {
					label: "Form",
					type: "array",
					items: {
						label: "string",
						type: "string"
					}
				},
				choice: {
					label: "Choices",
					type: "array",
					items: {
						label: "string",
						type: "string"
					}
				}
			}
		},
		object: {
			label: "Empty object",
			type: "object",
			properties: {}
		},
	}
}
