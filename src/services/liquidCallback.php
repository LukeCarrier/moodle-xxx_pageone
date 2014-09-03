<?php
require_once('../../../config.php');
require_once('callback_url.php');
global $CFG;
header("Content-Type: application/wsdl+xml");
?>
<!-- Published by JAX-WS RI at http://jax-ws.dev.java.net. RI's version is Oracle JAX-WS 2.1.5. -->
<WL5G3N0:definitions xmlns="" xmlns:WL5G3N0="http://schemas.xmlsoap.org/wsdl/" xmlns:WL5G3N1="http://jaxb.liquidcallbackv3.pageone.com" xmlns:WL5G3N2="http://schemas.xmlsoap.org/wsdl/soap/" targetNamespace="http://jaxb.liquidcallbackv3.pageone.com">
  <WL5G3N0:types>
    <xsd:schema xmlns:enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:intf="http://jaxb.liquidcallbackv3.pageone.com" xmlns:jaxb="http://java.sun.com/xml/ns/jaxb" xmlns:ov="http://jaxb.liquidcallbackv3.pageone.com" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xjc="http://java.sun.com/xml/ns/jaxb/xjc" xmlns:xsd="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified" jaxb:extensionBindingPrefixes="xjc" jaxb:version="2.0" targetNamespace="http://jaxb.liquidcallbackv3.pageone.com">
      <xsd:annotation>
        <xsd:appinfo>
          <jaxb:globalBindings>
            <xjc:serializable/>
          </jaxb:globalBindings>
        </xsd:appinfo>
      </xsd:annotation>
      <xsd:complexType name="onDeliveryReportType">
        <xsd:sequence>
          <xsd:element name="report">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="destination" type="xsd:string"/>
                <xsd:element name="receiptTime" type="xsd:dateTime"/>
                <xsd:element minOccurs="0" name="resultCode" type="xsd:int"/>
                <xsd:element name="source" type="xsd:string"/>
                <xsd:element minOccurs="0" name="transactionID" type="xsd:string"/>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="onMessageReceivedType">
        <xsd:sequence>
          <xsd:element name="message">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="destination" type="xsd:string"/>
                <xsd:element name="messageTime" type="xsd:dateTime"/>
                <xsd:element name="source" type="xsd:string"/>
                <xsd:element minOccurs="0" name="text" type="xsd:string"/>
                <xsd:element minOccurs="0" name="transactionID" type="xsd:string"/>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:element name="onDeliveryReport" type="ov:onDeliveryReportType"/>
      <xsd:element name="onMessageReceived" type="ov:onMessageReceivedType"/>
      <xsd:complexType name="positionType">
        <xsd:sequence>
          <xsd:element name="lattitude" type="xsd:double"/>
          <xsd:element name="longitude" type="xsd:double"/>
          <xsd:element minOccurs="0" name="postCode" type="xsd:string"/>
          <xsd:element minOccurs="0" name="streetAddress" type="xsd:string"/>
          <xsd:element name="time" type="xsd:dateTime"/>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="sourceType">
        <xsd:sequence>
          <xsd:element minOccurs="0" name="firstname" type="xsd:string"/>
          <xsd:element minOccurs="0" name="surname" type="xsd:string"/>
          <xsd:element name="address" type="xsd:string"/>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="statusType">
        <xsd:sequence>
          <xsd:element minOccurs="0" name="statusText" type="xsd:string"/>
          <xsd:element minOccurs="0" name="statusID" type="xsd:int"/>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="destinationType">
        <xsd:sequence>
          <xsd:element minOccurs="0" name="firstname" type="xsd:string"/>
          <xsd:element minOccurs="0" name="surname" type="xsd:string"/>
          <xsd:element name="address" type="xsd:string"/>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="onPagerMessageReceivedType">
        <xsd:sequence>
          <xsd:element name="pagerMessage">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element minOccurs="0" name="battery" type="xsd:string"/>
                <xsd:element minOccurs="0" name="delivered" type="xsd:int"/>
                <xsd:element minOccurs="0" name="destination" type="ov:destinationType"/>
                <xsd:element minOccurs="0" name="message_read" type="xsd:int"/>
                <xsd:element minOccurs="0" name="position" type="ov:positionType"/>
                <xsd:element minOccurs="0" name="request_text" type="xsd:string"/>
                <xsd:element minOccurs="0" name="response_text" type="xsd:string"/>
                <xsd:element minOccurs="0" name="source" type="ov:sourceType"/>
                <xsd:element minOccurs="0" name="status" type="ov:statusType"/>
                <xsd:element minOccurs="0" name="time_received" type="xsd:dateTime"/>
                <xsd:element minOccurs="0" name="transactionID" type="xsd:string"/>
                <xsd:element minOccurs="0" name="type" type="xsd:int"/>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:sequence>
      </xsd:complexType>
      <xsd:element name="onPagerMessageReceived" type="ov:onPagerMessageReceivedType"/>
    </xsd:schema>
  </WL5G3N0:types>
  <WL5G3N0:message name="onDeliveryReportRequest">
    <WL5G3N0:part element="WL5G3N1:onDeliveryReport" name="receipt"/>
  </WL5G3N0:message>
  <WL5G3N0:message name="onMessageReceivedRequest">
    <WL5G3N0:part element="WL5G3N1:onMessageReceived" name="message"/>
  </WL5G3N0:message>
  <WL5G3N0:message name="onPagerMessageReceivedRequest">
    <WL5G3N0:part element="WL5G3N1:onPagerMessageReceived" name="message"/>
  </WL5G3N0:message>
  <WL5G3N0:portType name="CallbackServicePortType">
    <WL5G3N0:operation name="onDeliveryReport">
      <WL5G3N0:input message="WL5G3N1:onDeliveryReportRequest" name="onDeliveryReportRequest"/>
    </WL5G3N0:operation>
    <WL5G3N0:operation name="onMessageReceived">
      <WL5G3N0:input message="WL5G3N1:onMessageReceivedRequest" name="onMessageReceivedRequest"/>
    </WL5G3N0:operation>
    <WL5G3N0:operation name="onPagerMessageReceived">
      <WL5G3N0:input message="WL5G3N1:onPagerMessageReceivedRequest" name="onPagerMessageReceivedRequest"/>
    </WL5G3N0:operation>
  </WL5G3N0:portType>
  <WL5G3N0:binding name="BasicHttpBinding_CallbackServicePortType" type="WL5G3N1:CallbackServicePortType">
    <WL5G3N2:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <WL5G3N0:operation name="onDeliveryReport">
      <WL5G3N2:operation soapAction="onDeliveryReport" style="document"/>
      <WL5G3N0:input name="onDeliveryReportRequest">
        <WL5G3N2:body use="literal"/>
      </WL5G3N0:input>
    </WL5G3N0:operation>
    <WL5G3N0:operation name="onMessageReceived">
      <WL5G3N2:operation soapAction="onMessageReceived" style="document"/>
      <WL5G3N0:input name="onMessageReceivedRequest">
        <WL5G3N2:body use="literal"/>
      </WL5G3N0:input>
    </WL5G3N0:operation>
    <WL5G3N0:operation name="onPagerMessageReceived">
      <WL5G3N2:operation soapAction="onPagerMessageReceived" style="document"/>
      <WL5G3N0:input name="onPagerMessageReceivedRequest">
        <WL5G3N2:body use="literal"/>
      </WL5G3N0:input>
    </WL5G3N0:operation>
  </WL5G3N0:binding>
  <WL5G3N0:service name="CallbackServiceV3">
    <WL5G3N0:port binding="WL5G3N1:BasicHttpBinding_CallbackServicePortType" name="BasicHttpBinding_CallbackServicePortType">
      <WL5G3N2:address location="<?php
                            global $CFG;
                            if ($CFG->block_pageone_https==true && strpos($CALLBACK_URL, "http://")>-1)
                                echo str_replace("http://", "https://", $CALLBACK_URL);
                            else
                                echo $CALLBACK_URL;?>"/>
    </WL5G3N0:port>
  </WL5G3N0:service>
</WL5G3N0:definitions>
