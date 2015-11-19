package routines;

import java.lang.reflect.Field;

/*
 * user specification: the function's comment should contain keys as follows: 1. write about the function's comment.but
 * it must be before the "{talendTypes}" key.
 * 
 * 2. {talendTypes} 's value must be talend Type, it is required . its value should be one of: String, char | Character,
 * long | Long, int | Integer, boolean | Boolean, byte | Byte, Date, double | Double, float | Float, Object, short |
 * Short
 * 
 * 3. {Category} define a category for the Function. it is required. its value is user-defined .
 * 
 * 4. {param} 's format is: {param} <type>[(<default value or closed list values>)] <name>[ : <comment>]
 * 
 * <type> 's value should be one of: string, int, list, double, object, boolean, long, char, date. <name>'s value is the
 * Function's parameter name. the {param} is optional. so if you the Function without the parameters. the {param} don't
 * added. you can have many parameters for the Function.
 * 
 * 5. {example} gives a example for the Function. it is optional.
 */
public class ConversionString {
/*
 * 
int comptRecolteChoices = ((String[]) globalMap.get("tMemorizeRows_6_fieldName")).length;
for (int i=0; i<comptRecolteChoices;i++) {
	if (((String[]) globalMap.get("tMemorizeRows_6_fieldName"))[i]!=null) {
	java.lang.reflect.Field field = recolte_to_csv.getClass().getField(StringHandling.UPCASE(((String[]) globalMap.get("tMemorizeRows_6_fieldName"))[i]));
	String value = ((String[]) globalMap.get("tMemorizeRows_6_data"))[i];
	String fieldName = StringHandling.UPCASE(((String[]) globalMap.get("tMemorizeRows_6_fieldName"))[i]);
	String data = ((String[]) globalMap.get("tMemorizeRows_6_data"))[i];
	//recolte_to_csv.getClass().getField(StringHandling.UPCASE(((String[]) globalMap.get("tMemorizeRows_6_fieldName"))[i])).set(recolte_to_csv, ((String[]) globalMap.get("tMemorizeRows_6_data"))[i]);
	ConversionString.setStringValue(recolte_to_csv, fieldName, data);
	}
}
 */
	public static void compileChoices(Object ObjArray) {
		System.out.println(Dumper.dump(ObjArray));
	}
	public static void setChoices(Object obj, String[] fieldsName, String[] datas) {
		int comptChoices = fieldsName.length;
		for (int i=0; i<comptChoices;i++) {
			if (fieldsName[i]!=null) {
				setStringValue(obj, StringHandling.UPCASE(fieldsName[i]), datas[i]);
			}
		}
		//return obj;
	}
	private static Object setStringValue(Object obj, String fieldName, String value) {
		Field field = null;
		try {
			field = obj.getClass().getField(fieldName);
		} catch (NoSuchFieldException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SecurityException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

		try {
			field.setAccessible(true);
			switch (field.getGenericType().toString()) {
				case "class java.lang.Integer" :
					field.set(obj, Integer.parseInt(value));
					break;
				case "class java.lang.String" :
					field.set(obj, value);
					break;
			}
		} catch (NumberFormatException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IllegalArgumentException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IllegalAccessException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
			System.out.println(e.toString());
		}

		return obj;
	}
    /**
     * helloExample: not return value, only print "hello" + message.
     * 
     * 
     * {talendTypes} String
     * 
     * {Category} User Defined
     * 
     * {param} string("world") input: The string need to be printed.
     * 
     * {example} helloExemple("world") # hello world !.
     */
    public static void helloExample(String message) {
        if (message == null) {
            message = "World"; //$NON-NLS-1$
        }
        System.out.println("Hello " + message + " !"); //$NON-NLS-1$ //$NON-NLS-2$
    }
}
